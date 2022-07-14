<?php

use App\Task;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     error_log("INFO: get /");
//     return view('tasks', [
//         'tasks' => Task::orderBy('created_at', 'asc')->get()
//     ]);
// });

/**
    * Show Task Dashboard
    */
    Route::get('/task', function () {
        error_log("INFO: get /");
        return view('tasks', [
            'tasks' => Task::orderBy('created_at', 'asc')->get()
        ]);
    });


/**
    * Add New Task
    */
    Route::post('/task', function (Request $request) {
        error_log("INFO: post /task");
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);
    
        if ($validator->fails()) {
            error_log("ERROR: Add task failed.");
            return redirect('/')
                ->withInput()
                ->withErrors($validator);
        }
    
        $task = new Task;
        $task->name = $request->name;
        $task->save();
    
        return redirect('/');
    });

Route::get('/{file}', function ($file) {
    $filename = "$file";

    $disk = Storage::disk('azure');

    if (!$disk->exists($filename))
    {
            abort(404);
    }

    $contents = $disk->get($filename);

    return response($contents)->header('content-Disposition','attachment');
});

Route::get('/', function() {
    $path = '';

    // Get the Larvel disk for Azure
    $disk = \Storage::disk('azure');

    // List files in the container path
    $files = $disk->files($path);

    // create an array to store the names, sizes and last modified date
    $list = array();

    // Process each filename and get the size and last modified date
    foreach($files as $file) {
            $size = $disk->size($file);

            $modified = $disk->lastModified($file);
            $modified = date("Y-m-d H:i:s", $modified);

            $filename = "$path/$file";

            $item = array(
                    'name' => $filename,
                    'size' => $size,
                    'modified' => $modified,
            );

            array_push($list, $item);
    }

    return view('tasks', [
        'files' => $list,'tasks' => Task::orderBy('created_at', 'asc')->get()
    ]);

    // $results = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // return response($results)->header('content-type', 'application/json');
});

Route::post('/task1', function (Request $req) {
    // error_log("INFO: post /task");
    // $validator = Validator::make($request->all(), [
    //     'name' => 'required|max:255',
    // ]);

    // if ($validator->fails()) {
    //     error_log("ERROR: Add task failed.");
    //     return redirect('/')
    //         ->withInput()
    //         ->withErrors($validator);
    // }
    


    if($req->file()) {
        $fileName = time().'_'.$req->file->getClientOriginalName();
        // save file to azure blob virtual directory uplaods in your container
        $filePath = $req->file('file')->storeAs('', $fileName, 'azure');

        return back()
        ->with('success','File has been uploaded.');

    }

    return redirect('/');
});

/**
    * Delete Task
    */
    Route::delete('/task/{id}', function ($id) {
        error_log('INFO: delete /task/'.$id);
        Task::findOrFail($id)->delete();
    
        return redirect('/');
    });

