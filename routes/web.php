<?php

Route::get('/', 'HomeController@home')->name('home');
Route::get('/{qid}', 'ItemController@item')->where('qid', '[Qq][0-9]+')->name('item');
Route::get('/search', 'ItemController@search')->name('search');
