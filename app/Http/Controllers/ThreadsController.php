<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Thread;
use App\Http\Requests\ThreadRequest;

use App\Events\NewThread;

class ThreadsController extends Controller
{

    public function home()
    {
        $can_pin = false;
        $isAdmin = false;
        if (\Auth::user()) {
            $user    = \Auth::user();
            $isAdmin = $user->can('pin', Thread::class);
        }
        return view('threads.index', compact('isAdmin'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $threads = Thread::orderBy('pinned', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate();
        return response()->json($threads);
    }

    public function edit(Thread $thread)
    {
        return view('threads.edit', compact('thread'));
    }

    public function show($id)
    {
        $thread = Thread::findOrFail($id);
        return view('threads.show', compact('thread'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ThreadRequest $request)
    {
        $thread          = new Thread;
        $thread->title   = $request->title;
        $thread->body    = $request->body;
        $thread->user_id = Auth::user()->id;
        $thread->save();

        broadcast(new NewThread($thread));

        return response()->json([
            'created' => 'success',
            'data' => $thread->toArray()
        ], 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(ThreadRequest $request, Thread $thread)
    {
        $thread->title = $request->input('title');
        $thread->body  = $request->input('body');
        $thread->save();
        return redirect()->route('thread.show', $thread);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy(Thread $thread)
    {
        //
    }

    public function pinner(Thread $thread)
    {
        $this->authorize('pin', Thread::class);

        Thread::where('id', '<>', $thread->id)
                ->update(['pinned' => false]);

        $thread->pinned = !$thread->pinned;
        $thread->save();

        return back();
    }

    public function closer(Thread $thread)
    {
        $this->authorize('close', Thread::class);

        $thread->closed = !$thread->closed;
        $thread->save();

        return back();
    }
}
