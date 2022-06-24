<?php

namespace NewIDC\Virtualmin;

use GuzzleHttp\Client;
use NewIDC\Plugin\Server;

class Plugin extends Server
{
    protected $name = 'Virtualmin';

    protected $composer = 'newidc/virtualmin';

    protected $description = 'Virtualmin对接插件';

    private function exec($program, array $data)
    {
        $protocol = $this->server->api_access_ssl ? 'https://' : 'http://';
        $client = new Client([
            'base_uri' => $protocol . $this->getHost() . ':' . $this->getPort(),
            'verify' => false,
            'auth' => [$this->server->username, $this->server->password]
        ]);
        $query = [
            'program' => $program,
            'json' => 1,
            'multiline' => ''
        ];
        $query = array_merge($query, $data);
        return json_decode($client->get('/virtual-server/remote.cgi', ['query' => $query])
            ->getBody()->getContents(), true);
    }

    /**
     * @inheritDoc
     */
    public function activate()
    {
        $result = $this->exec('create-domain', [
            'domain' => $this->service->domain,         // 必填
            'user' => $this->service->username,         // 虽然可以不填但是会跟系统不一样，所以还是填一下好
            'pass' => $this->service->password,         // 必填
            'desc' => 'NewIDC #' . $this->service->id,  // 选填
            'features-from-plan' => '',   // 后面value都留空，表示option
            'limits-from-plan' => ''
        ]);
        if (!isset($result['status']) || $result['status'] != 'success') {
            return ['code' => 1, 'msg' => $result['error'] ?? '未知错误'];
        } else {
            return ['code' => 0];
        }
    }

    /**
     * @inheritDoc
     */
    public function suspend()
    {
        $result = $this->exec('disable-domain', [
            'why' => $this->service->extra['suspend_reason'],
            'domain' => $this->service->domain,
            'subservers' => ''
        ]);
        if (!isset($result['status']) || $result['status'] != 'success') {
            return ['code' => 1, 'msg' => $result['error'] ?? '未知错误'];
        } else {
            return ['code' => 0];
        }
    }

    /**
     * @inheritDoc
     */
    public function unsuspend()
    {
        $result = $this->exec('enable-domain', [
            'domain' => $this->service->domain,
            'subservers' => ''
        ]);
        if (!isset($result['status']) || $result['status'] != 'success') {
            return ['code' => 1, 'msg' => $result['error'] ?? '未知错误'];
        } else {
            return ['code' => 0];
        }
    }

    /**
     * @inheritDoc
     */
    public function terminate()
    {
        $result = $this->exec('delete-domain', [
            'user' => $this->service->username
        ]);
        if (!isset($result['status']) || $result['status'] != 'success') {
            return ['code' => 1, 'msg' => $result['error'] ?? '未知错误'];
        } else {
            return ['code' => 0];
        }
    }

    /**
     * @inheritDoc
     */
    public function changePassword($password)
    {
        $result = $this->exec('modify-domain', [
            'domain' => $this->service->domain,
            'pass' => $password
        ]);
        if (!isset($result['status']) || $result['status'] != 'success') {
            return ['code' => 1, 'msg' => $result['error'] ?? '未知错误'];
        } else {
            return ['code' => 0];
        }
    }

    /**
     * @inheritDoc
     */
    public function upgradeDowngrade()
    {
        // TODO: Implement upgradeDowngrade() method.
    }

    public function userLogin()
    {
        $protocol = $this->server->access_ssl ? 'https' : 'http';
        return <<<EOT
<form method="post" action="$protocol://{$this->getHost(false)}:{$this->getPort()}/session_login.cgi" target="_blank">
<input type="hidden" name="username" value="{$this->service->username}">
<input type="hidden" name="password" value="{$this->service->password}">
<input type="submit" value="登录面板" class="btn btn-success">
</form>
EOT;
    }

    public function adminLogin()
    {
        $protocol = $this->server->access_ssl ? 'https' : 'http';
        return <<<EOT
<form method="post" action="$protocol://{$this->getHost(false)}:{$this->getPort()}/session_login.cgi" target="_blank">
<input type="hidden" name="username" value="{$this->server->username}">
<input type="hidden" name="password" value="{$this->server->password}">
<input type="submit" value="Virtualmin" class="btn btn-success">
</form>
EOT;
    }
}