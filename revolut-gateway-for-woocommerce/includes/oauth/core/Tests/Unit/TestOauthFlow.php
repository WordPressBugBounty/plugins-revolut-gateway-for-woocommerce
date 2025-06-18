<?php

namespace Revolut\Plugin\Core\Tests\Unit\TestOauthFlow;

use Revolut\Plugin\Core\Infrastructure\ConfigProvider;
use Revolut\Plugin\Core\Infrastructure\TokenRepositoryInterface;
use Revolut\Plugin\Core\Infrastructure\HttpClientInterface;
use Revolut\Plugin\Core\Interfaces\Services\LockInterface;
use Revolut\Plugin\Core\Infrastructure\LoggerInterface;
use Revolut\Plugin\Core\Models\Token;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnect;
use Revolut\Plugin\Core\Services\RLog;
use Revolut\Plugin\Core\Exceptions\TokenRefreshInProgressException;

use PHPUnit\Framework\TestCase;

class InMemoryTokenRepository implements TokenRepositoryInterface {
    public $token = null;
    public function saveTokens(Token $token) { $this->token = $token; }
    public function getTokens(){ return $this->token; }
}

class TestHttpClient implements HttpClientInterface {
    public $capturedUrl;
    public $response = [];
    public function post($url, $params) {
        $this->capturedUrl = $url;
        return $this->response;
    }
}

class TestLock implements LockInterface {
    private $locked = false;
    public function acquire() { if ($this->locked) return false; $this->locked = true; return true; }
    public function release() { $this->locked = false; }
    public function isLocked() { return $this->locked; }
}

class TestLogger implements LoggerInterface {
    const LOG_CONTEXT = array( 'source' => 'revolut-gateway' );
    
    private $logger = [];

    public function info(string $message, mixed $context = self::LOG_CONTEXT){
        array_push($this->logger, "[INFO]  " . $message);
    }
    
    public function error(string $message, mixed $context = self::LOG_CONTEXT){
        array_push($this->logger, "[ERROR]  " . $message);

    }
    
    public function debug(string $message, mixed $context = self::LOG_CONTEXT){
        array_push($this->logger, "[DEBUG]  " . $message);
    }
}

RLog::setLogger(new TestLogger(), array( 'source' => 'revolut-gateway-for-woocommerce-test' ));

class AuthConnectTest extends TestCase {
    
    public function testExchangeAuthorizationCodeStoresTokensAndCallsEndpointWithSecrets() {
        $repo = new InMemoryTokenRepository();
        $http = new TestHttpClient();
        $http->response = ['access_token' => 'A', 'refresh_token' => 'R'];
        
        $onDemandRefreshLock = new TestLock();
        $jobRefreshLock = new TestLock();
        
        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $onDemandRefreshLock, $jobRefreshLock);

        $token = $svc->exchangeAuthorizationCode(ConfigProvider::DEV, 'code1', 'ver1');
        $this->assertEquals('A', $token->accessToken);
        $this->assertEquals('R', $token->refreshToken);
        $this->assertEquals($token, $repo->getTokens());
        $this->assertEquals('https://merchant.revolut.codes/api/oauth/token', $http->capturedUrl);
    }

    public function testRefreshTokenUpdatesTokensAndCallsEndpointWithSecrets() {
        $repo = new InMemoryTokenRepository();
        $repo->saveTokens(new Token('oldA', 'oldR'));
        $http = new TestHttpClient();
        $http->response = ['access_token' => 'newA', 'refresh_token' => 'newR'];
        
        $onDemandRefreshLock = new TestLock();
        $jobRefreshLock = new TestLock();
        
        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $onDemandRefreshLock, $jobRefreshLock);

        $svc->refreshToken(ConfigProvider::PROD);
        $token = $repo->getTokens(new Token('oldA', 'oldR'));

        $this->assertEquals('newA', $token->accessToken);
        $this->assertEquals('newR', $token->refreshToken);
        $this->assertEquals($token, $repo->getTokens());
        $this->assertEquals('https://merchant.revolut.com/api/oauth/token', $http->capturedUrl);
    }

    public function testRefreshTokenThrowsIfAlreadyLocked() {
        $repo = new InMemoryTokenRepository();
        $repo->saveTokens(new Token('A', 'R'));
        $http = new TestHttpClient();
        $http->response = ['access_token' => 'B', 'refresh_token' => 'S'];
        $lock = new TestLock();
        $lock->acquire(); // Simulate lock already held
        
        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $lock, $lock);

        $this->expectException(TokenRefreshInProgressException::class);
        $this->expectExceptionMessage('token refresh in progress...');
        $svc->refreshToken(ConfigProvider::PROD);
    }
    
    public function testRefreshTokenSucceedWhileRefreshJobIsInProgress() {
        $repo = new InMemoryTokenRepository();
        $repo->saveTokens(new Token('A', 'R'));
        
        $http = new TestHttpClient();
        $http->response = ['access_token' => 'B', 'refresh_token' => 'S'];
       
        $jobRefreshLock = new TestLock();
        $jobRefreshLock->acquire();

        $onDemandRefreshLock = new TestLock();
        
        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $onDemandRefreshLock, $jobRefreshLock);
        
        $svc->refreshToken(ConfigProvider::DEV);
        $token = $repo->getTokens();
        $this->assertEquals('B', $token->accessToken);
        $this->assertEquals('S', $token->refreshToken);

        $this->assertEquals($token, $repo->getTokens());
    }

    public function testRefreshTokenJobThrowsIfOnDemandRefreshIsInProgresss() {
        $repo = new InMemoryTokenRepository();
        $repo->saveTokens(new Token('A', 'R'));
        $http = new TestHttpClient();
        $http->response = ['access_token' => 'B', 'refresh_token' => 'S'];
       
        $jobRefreshLock = new TestLock();
        
        $onDemandRefreshLock = new TestLock();
        $onDemandRefreshLock->acquire();
        
        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $onDemandRefreshLock, $jobRefreshLock);

        $this->expectException(TokenRefreshInProgressException::class);
        $this->expectExceptionMessage('token refresh in progress...');
        
        $svc->refreshToken(ConfigProvider::DEV);
    }

    public function testRefreshFailsWithoutStoredToken() {
        $repo = new InMemoryTokenRepository();
        $http = new TestHttpClient();
        
        $tokenServiceLock = new TestLock();
        $tokenJobLock = new TestLock();

        $provider = new ConfigProvider();
        $svc = new AuthConnect($repo, $http, $provider, $tokenServiceLock, $tokenJobLock);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No refresh token stored');
        $svc->refreshToken(ConfigProvider::PROD);
    }
}