<?php

namespace App\Core\Model;

trait Searchable
{
    /**
     * Search Scope
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        $searchable = $this->getSearchableColumns();

        $query->where(function($subQuery) use($searchable, $search) {
            foreach ($searchable as $relation => $column)
            {
                if(is_array($column)) {
                    // here we join table for each relation
                    $subQuery->orWhereHas($relation, function($q) use ($column, $search) {
                        // here we need to use nested where like: ... WHERE key = fk AND (x LIKE y OR z LIKE y)
                        $q->where(function($q) use ($column, $search) {
                            foreach($column as $relatedField) {
                                $q->where($relatedField, 'LIKE', '%' . $search . '%');
                            }
                        });
                    });
                } else {
                    $subQuery->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            }
        });

        return $query;
    }

    /**
     * Get Searchable columns
     * @return array
     */
    protected function getSearchableColumns()
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }
}
