<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->render('admin/dashboard', ['user' => Session::get('user')]);
    }
}