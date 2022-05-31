<?php

namespace addons\nkeditor\controller;

use app\common\model\Attachment;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;
use think\addons\Controller;

class Index extends Controller
{

    public function index()
    {
        $this->error('该插件暂无前台页面');
    }

    /**
     * 文件列表
     */
    public function attachment()
    {
        $model = new Attachment;
        $page = $this->request->request('page');
        $fileType = $this->request->request('fileType');
        $module = $this->request->param('module');
        $pagesize = 15;
        $config = get_addon_config('nkeditor');
        $type = [];
        $imageSuffix = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'svg'];
        if ($fileType == 'image') {
            $type = $imageSuffix;
        } elseif ($fileType == 'flash') {
            $type = ['swf', 'flv'];
        } elseif ($fileType == 'media') {
            $type = ['swf', 'flv', 'mp4', 'mpeg', 'mp3', 'wav', 'ogg', 'acc', 'webm'];
        } elseif ($fileType == 'file') {

        }
        if ($module == 'admin') {
            $auth = \app\admin\library\Auth::instance();
            if (!$auth->id) {
                $this->error('请登录后再操作!');
            } else {
                $mode = $config['attachmentmode_admin'];
            }
            if ($mode == 'all') {

            } else {
                if (!$auth->isSuperAdmin()) {
                    $adminIds = $mode == 'auth' ? $auth->getChildrenAdminIds(true) : [$auth->id];
                    $model->where('admin_id', 'in', $adminIds);
                }
            }
        } else {
            if (!$this->auth->id) {
                $this->error('请登录后再操作!');
            } else {
                $mode = $config['attachmentmode_index'];
            }
            if ($mode == 'all') {

            } else {
                $model->where('user_id', 'in', [$this->auth->id]);
            }
        }

        if ($type) {
            $model->where('imagetype', 'in', $type);
        }

        $list = $model
            ->order('id', 'desc')
            ->paginate($pagesize);

        $items = $list->items();
        $data = [];
        foreach ($items as $k => &$v) {
            $v['imagetype'] = strtolower($v['imagetype']);
            $v['fullurl'] = cdnurl($v['url'], true);
            $thumbUrl = addon_url("nkeditor/index/preview") . "?suffix=" . $v['imagetype'];
            $data[] = [
                'width'    => $v['imagewidth'],
                'height'   => $v['imageheight'],
                'filename' => $v['filename'],
                'filesize' => $v['filesize'],
                'oriURL'   => $v['fullurl'],
                'thumbURL' => $v['fullurl'],
            ];
        }
        $result = [
            'code'     => '000',
            'count'    => $list->total(),
            'page'     => $page,
            'pagesize' => $pagesize,
            'extra'    => '',
            'data'     => $data
        ];
        return json($result);
    }

    /**
     * 下载图片
     */
    public function download()
    {
        $url = $this->request->request("url");
        $contentType = '';
        try {
            $client = new Client();
            $response = $client->request('GET', $url, ['stream' => true, 'verify' => false, 'allow_redirects' => ['strict' => true]]);

            $body = $response->getBody();
            $contentType = $response->getHeader('Content-Type');
            $contentType = $contentType[0] ?? 'image/png';
        } catch (\Exception $e) {
            $this->error("图片下载失败");
        }

        $contentTypeArr = explode('/', $contentType);
        if ($contentTypeArr[0] !== 'image') {
            $this->error("只支持图片文件");
        }

        $response = new StreamedResponse(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(1024);
            }
        });
        $response->headers->set('Content-Type', $contentType);
        $response->send();
        return;
    }

}
