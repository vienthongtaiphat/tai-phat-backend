<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Storage;

class LogController extends Controller
{
    public function getLogDetail(Request $request)
    {
        $fileName = $request->fileName ?? '';
        $contents = Storage::disk('logs')->get($fileName);
        return response()->json([
            'message' => $contents,
        ]);
    }

    public function getLog($fileName)
    {
        $contents = Storage::disk('logs')->get($fileName . '.log');

        $fileName = "logs.txt";

        // use headers in order to generate the download
        $headers = [
            'Content-type' => 'text/plain',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            'Content-Length' => strlen($contents),
        ];

        // make a response, with the content, a 200 response code and the headers
        return Response::make($contents, 200, $headers);
    }

    public function removeLog(Request $request)
    {
        $fileName = $request->fileName ?? '';
        if ($fileName === 'all') {
            $files = Storage::disk('logs')->files('');
            $contents = Storage::disk('logs')->delete($files);
        } else {
            $contents = Storage::disk('logs')->delete($fileName);
        }

        return response()->json([
            'message' => 'Delete log success',
        ]);
    }
}
