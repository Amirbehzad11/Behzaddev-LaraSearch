<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    public function scopeAdvancedSearch(Builder $query, array $params = []): Builder
    {
        $searchTerm = $params['query'] ?? null;
        $filters = $params['filters'] ?? [];
        $orderField = $params['order_field'] ?? 'created_at';
        $orderType = $params['order_type'] ?? 'DESC';
        $specificSearchFields = $params['specific_search'] ?? [];
        $start_date = $params['start_date'] ?? null;
        $end_date = $params['end_date'] ?? null;

        if (!empty($searchTerm)) {
            $query->where(function (Builder $q) use ($searchTerm) {
                $this->searchInModel($q, $searchTerm);
                $this->searchInRelations($q, $searchTerm);
            });
        }

        if (!empty($start_date) || !empty($end_date)) {
            $this->getBetweenDate($query, $start_date, $end_date);
        }

        $this->applySpecificSearch($query, $specificSearchFields);
        $this->applyAdvancedFilters($query, $filters);
        $this->applyOrderBy($query, $orderField, $orderType);

        return $query;
    }


    protected function getBetweenDate(Builder $query, $start_date, $end_date): void
    {
        if (gettype($start_date) == "string") {
            $start_date = date('Y-m-d H:i:s', $start_date);
            $end_date = date('Y-m-d H:i:s', $end_date);
        }
        foreach ($this->dateSearchableRelations as $relation => $field) {
            $query->whereHas($relation, function (Builder $q) use ($field, $start_date, $end_date) {
                if (!empty($start_date) && !empty($end_date)) {
                    $q->whereBetween($field, [$start_date, $end_date]);
                } elseif (!empty($start_date)) {
                    $q->where($field, '>=', $start_date);
                } elseif (!empty($end_date)) {
                    $q->where($field, '<=', $end_date);
                }
            });
        }
    }



    protected function searchInModel(Builder $query, string $searchTerm): void
    {
        foreach ($this->getSearchableFields() as $field) {
            $query->orWhere($field, 'LIKE', "%{$searchTerm}%");
        }
    }

    protected function searchInRelations(Builder $query, string $searchTerm): void
    {
        foreach ($this->getSearchableRelations() as $relation => $fields) {
            $query->orWhereHas($relation, function (Builder $q) use ($fields, $searchTerm) {
                $q->where(function (Builder $subQ) use ($fields, $searchTerm) {
                    foreach ($fields as $field) {
                        $subQ->orWhere($field, 'LIKE', "%{$searchTerm}%");
                    }
                });
            });
        }
    }

    protected function applySpecificSearch(Builder $query, array $specificSearchFields): void
    {
        foreach ($specificSearchFields as $field => $value) {
            if ($value !== null) {
                $relation = $this->getRelationForField($field);
                if ($relation) {
                    $query->whereHas($relation, function ($q) use ($field, $value) {
                        $q->where($field,"LIKE", "%{$value}%");
                    });
                } else {
                    $query->where($field,"LIKE", "%{$value}%");
                }
            }
        }
    }

    protected function getRelationForField(string $field): ?string
    {
        foreach ($this->getSearchableRelations() as $relation => $fields) {
            if (in_array($field, $fields)) {
                return $relation;
            }
        }
        return null;
    }

    protected function applyAdvancedFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && in_array($field, $this->getFilterableFields())) {
                $query->where($field, $value);
            }
        }
    }

    protected function applyOrderBy(Builder $query, ?string $orderField, string $orderType): void
    {
        $orderField = $orderField ?? 'id';
        $orderType = in_array(strtoupper($orderType), ['ASC', 'DESC']) ? strtoupper($orderType) : 'DESC';

        if (in_array($orderField, $this->getOrderableFields())) {
            $query->orderBy($orderField, $orderType);
        } else {
            $query->orderBy('id', 'DESC');
        }
    }

    protected function getSearchableFields(): array
    {
        return $this->searchable ?? [];
    }

    protected function getSearchableRelations(): array
    {
        return $this->searchableRelations ?? [];
    }

    protected function getFilterableFields(): array
    {
        return $this->filterable ?? $this->fillable ?? [];
    }

    protected function getOrderableFields(): array
    {
        return $this->orderable ?? $this->fillable ?? ['id'];
    }

    public static function advancedSearch(array $params = [], bool $paginate = true, ?\Closure $additionalQuery = null)
    {
        $query = static::query();
        $query->advancedSearch($params);
        if ($additionalQuery) {
            $query = $additionalQuery($query);
        }
        $perPage = $params['per_page'] ?? 15;
        return $paginate ? $query->paginate($perPage) : ['data' => $query->get()];
    }
}
