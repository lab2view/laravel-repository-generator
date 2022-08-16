<?php

namespace Lab2view\RepositoryGenerator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    /**
     * Create a new repository instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $withTrashed
     * @return bool
     */
    public function exists(string $key, $value, bool $withTrashed = false): bool
    {
        try {
            $query = $this->model->where($key, $value);
            if ($withTrashed) {
                $query = $query->hasMacro('withTrashed') ? $query->withTrashed() : $query;
            }

            return $query->exists();
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param string $attr_name
     * @param mixed $attr_value
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed|null
     */
    public function getByAttribute(
        string $attr_name,
        $attr_value,
        array $relations = [],
        bool $withTrashed = false,
        array $selects = []
    ) {
        try {
            $query = $this->initiateQuery($relations, $withTrashed, $selects);
            return $query->where($attr_name, $attr_value)->first();
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param int $n
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getPaginate(int $n, array $relations = [], bool $withTrashed = false, array $selects = [])
    {
        $query = $this->initiateQuery($relations, $withTrashed, $selects);
        return $query->paginate($n);
    }

    /**
     * @param array $inputs
     * @return mixed
     */
    public function store(array $inputs)
    {
        try {
            return $this->model->create($inputs);
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $id
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getById($id, array $relations = [], bool $withTrashed = false, array $selects = [])
    {
        try {
            $query = $this->initiateQuery($relations, $withTrashed, $selects);
            return $query->find($id);
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function search($key, $value, array $relations = [], bool $withTrashed = false, array $selects = [])
    {
        $query = $this->initiateQuery($relations, $withTrashed, $selects);
        return $query->where($key, 'like', '%' . $value . '%')
            ->get();
    }

    /**
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getAll(array $relations = [], bool $withTrashed = false, array $selects = [])
    {
        $query = $this->initiateQuery($relations, $withTrashed, $selects);
        return $query->get();
    }

    /**
     * @param bool $withTrashed
     * @return mixed
     */
    public function countAll(bool $withTrashed = false)
    {
        $query = $this->model;
        if ($withTrashed) {
            $query = $query->hasMacro('withTrashed') ? $query->withTrashed() : $query;
        }

        return $query->count();
    }

    /**
     * @param $key
     * @param string $attr
     * @return mixed
     */
    public function getAllSelectable($key, string $attr = 'id')
    {
        return $this->model->pluck($key, $attr);
    }

    /**
     * @param $id
     * @param array $inputs
     * @return mixed
     */
    public function update($id, array $inputs)
    {
        try {
            $model = $this->getById($id);
            if ($model) {
                $model->update($inputs);
                return $model->fresh();
            } else {
                return null;
            }
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function destroy($id) : bool
    {
        try {
            $data = $this->getById($id);
            return $data ? $data->delete() : false;
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function destroyAll() : bool
    {
        try {
            return $this->model->delete();
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function forceDelete($id) : bool
    {
        try {
            $data = $this->getById($id, [], true);
            return $data ? $data->forceDelete() : false;
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function restore($id) : bool
    {
        try {
            $data = $this->getById($id, [], true);
            return $data ? $data->restore() : false;
        } catch (QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return Builder
     */
    private function initiateQuery(array $relations = [], bool $withTrashed = false, array $selects = [])
    {
        $query = $this->model;
        if (count($relations) > 0) {
            $query = $query->with($relations);
        }

        if (count($selects) > 0) {
            $query = $query->select($selects);
        }

        if ($withTrashed) {
            $query = $query->hasMacro('withTrashed') ? $query->withTrashed() : $query;
        }

        return $query;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function destroyByIds(array $ids)
    {
        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }
}
