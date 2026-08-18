<?php

use App\Livewire\DailyRoutine;
use App\Livewire\TemplateDetail;
use App\Livewire\TemplateForm;
use App\Livewire\TemplateList;
use Illuminate\Support\Facades\Route;

Route::get('/', DailyRoutine::class)->name('home');

Route::get('/plantillas', TemplateList::class)->name('templates.index');
Route::get('/plantillas/crear', TemplateForm::class)->name('templates.create');
Route::get('/plantillas/{template}/editar', TemplateForm::class)->name('templates.edit');
Route::get('/plantillas/{template}', TemplateDetail::class)->name('templates.show');
