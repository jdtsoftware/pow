<?php

namespace JDT\Pow\Interfaces;

interface Shop {
    public function list($page = 1, $perPage = 15) :\ Illuminate\Pagination\Paginator;
}