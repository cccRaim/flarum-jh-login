<?php

namespace cccRaim\FlarumJHLogin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\HtmlResponse;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Forum\Auth\Registration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use LogicException;

class JHAuthController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    protected $view;

    /**
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response, Factory $view)
    {
        $this->response = $response;
        $this->view = $view;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function handle(Request $request): ResponseInterface
    {
        try {
            $identification = Arr::get($request->getParsedBody(), 'identification');
            $password = Arr::get($request->getParsedBody(), 'password');

            $user = $this->checkJHPassport($identification, $password);

            return $this->response->make(
                'jh',
                $user->pid,
                function (Registration $registration) use ($user) {
                    $registration
                        ->provideTrustedEmail($user->email)
                        ->suggestUsername($user->pid)
                        ->setPayload(get_object_vars($user));
                }
            );
        } catch (LogicException $e) {
            $view = $this->view->make('cccRaim.flarum-jh-login::error', [
                'message' => $e->getMessage(),
            ]);

            return new HtmlResponse($view->render());
        }
    }

    public function throwError($message) {
        throw new LogicException($message);
    }

    /**
     * 精弘用户中心登录验证
     *
     * @param string
     * @param string
     * @return boolean
     * @throws \cccRaim\FlarumJHLogin\JHAuthException
     */
    public function checkJHPassport($username, $password) {
        if (!$username OR !$password) {
            $this->throwError('请输入账号密码');
        }
        if (strstr($password, '../') != false) {
            $this->throwError('密码不允许带../');
        }
        $url = 'http://user.jh.zjut.edu.cn/api.php';
        $data = [
            'app' => 'passport',
            'action' => 'login',
            'passport' => $username,
            'password' => $password,
        ];
        if(!$content = $this->http_get($url, $data))
            $this->throwError('用户中心服务器错误');
        if(!$value = json_decode($content)) {
            $this->throwError('用户中心服务器错误');
        }
        if(isset($value->state) && $value->state == 'success') {
            return $value->data;
        } else {
            $this->throwError('用户名或密码错误');
        }
    }

    /**
     * 使用CURL的GET请求资源
     * @param  string   $url        资源路径
     * @param  array    $data  请求参数
     * @param  int      $timeout    超时时间，毫秒级
     * @return mixed
     */
    function http_get(string $url, array $data, int $timeout = 10000){//curl
        $ch = curl_init();
        if(!empty($data)){
            if(strpos($url, '?') == false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= http_build_query($data);
        }
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt ($ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }
}
