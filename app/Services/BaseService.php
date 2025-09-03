<?php


namespace App\Services;


class BaseService
{
	const PAGINATION_PER_PAGE_LIMIT = 1000;
	const PAGINATION_PER_PAGE_DEFAULT = 50;
	const PAGINATION_START_PAGE_DEFAULT = 1;

	protected function paginationOptions(int $page, int $perPage): array
	{
		$perPage = ($perPage > self::PAGINATION_PER_PAGE_LIMIT || !$perPage || $perPage < 1) ? self::PAGINATION_PER_PAGE_DEFAULT : $perPage;
		$page = ($page < 1 || !$page) ? self::PAGINATION_START_PAGE_DEFAULT : $page;
		$offset = ($page * $perPage) - $perPage;

		return ['page' => $page, 'per_page' => $perPage, 'offset' => $offset];
	}
}
