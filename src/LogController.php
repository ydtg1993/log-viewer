<?php

namespace Encore\Admin\LogViewer;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LogController extends Controller
{
    public function index($file = null, Request $request)
    {
        if ($file === null) {
            $path = $request->get('path');
            $file = (new LogViewer())->getLastModifiedLog($path);
        }

        return Admin::content(function (Content $content) use ($file, $request) {
            $path = $request->get('path');
            $offset = $request->get('offset');

            $viewer = new LogViewer($path,$file);

            $content->body(view('laravel-admin-logs::logs', [
                'logs'      => $viewer->fetch($offset),
                'logFiles'  => $viewer->getLogFiles($path,30),
                'fileName'  => $viewer->file,
                'end'       => $viewer->getFilesize(),
                'tailPath'  => route('log-viewer-tail', ['file' => $viewer->file]),
                'prevUrl'   => $viewer->getPrevPageUrl(),
                'nextUrl'   => $viewer->getNextPageUrl(),
                'filePath'  => $viewer->getFilePath($path),
                'size'      => static::bytesToHuman($viewer->getFilesize()),
            ]));

            $content->header($viewer->getFilePath());
        });
    }

    public function tail($file, Request $request)
    {
        $offset = $request->get('offset');

        $viewer = new LogViewer($file);

        list($pos, $logs) = $viewer->tail($offset);

        return compact('pos', 'logs');
    }

    protected static function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
