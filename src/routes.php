<?php

Route::get('receitaFederal/captcha/{document?}', ['as' => 'receita-federal.captcha', 'uses' => 'DouglasResende\ReceitaFederal\Controller\CaptchaController@index']);
Route::get('receitaFederal/processCNPJ', ['as' => 'receita-federal.processCNPJ', 'uses' =>'DouglasResende\ReceitaFederal\Controller\CNPJController@index']);
Route::get('receitaFederal/processCPF', ['as' => 'receita-federal.processCPF', 'uses' =>'DouglasResende\ReceitaFederal\Controller\CPFController@index']);