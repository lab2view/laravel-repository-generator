<?php

namespace Lab2view\RepositoryGenerator;

use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    /**
     * Create a new repository instance.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param int $n
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getPaginate(int $n, $relations = [], $withTrashed = false)
    {
        $query = $this->model;
        if (count($relations) > 0)
            $query = $query->with($relations);

        if ($withTrashed)
            $query = $query->withTrashed();

        return $query->paginate($n);
    }

    /**
     * @param array $inputs
     * @return mixed
     */
    public function store(Array $inputs)
    {
        try {
            return $this->model->create($inputs);
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $id
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getById($id, $relations = [], $withTrashed = false)
    {
        try {
            $query = $this->model;
            if (count($relations) > 0)
                $query = $query->with($relations);

            if ($withTrashed)
                $query = $query->withTrashed();

            return $query->find($id);
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function search($key, $value)
    {
        return $this->model
            ->where($key, 'like', '%' . $value . '%')
            ->get();
    }

    /**
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getAll(array $relations, $withTrashed = false)
    {
        $query = $this->model;
        if (count($relations) > 0)
            $query = $query->with($relations);

        if ($withTrashed)
            $query = $query->withTrashed();

        return $query->get();
    }

    /**
     * @param bool $withTrashed
     * @return mixed
     */
    public function countAll($withTrashed = false)
    {
        $query = $this->model;
        if ($withTrashed)
            $query = $query->withTrashed();

        return $query->count();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAllSelectable($key)
    {
        return $this->model->pluck($key, 'id');
    }

    /**
     * @param $id
     * @param array $inputs
     * @return mixed
     */
    public function update($id, Array $inputs)
    {
        try {
            $model = $this->getById($id);
            if ($model) {
                $model->update($inputs);
                return $model->fresh();
            } else
                return null;
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return null;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function destroy($id)
    {
        try {
            $data = $this->getById($id);
            return $data ? $data->delete() : false;
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function destroyAll()
    {
        try {
            return $this->model->delete();
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function forceDelete($id)
    {
        try {
            $data = $this->getById($id);
            return $data ? $data->forceDelete() : false;
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function restore($id)
    {
        try {
            $data = $this->getById($id);
            return $data ? $data->restore() : false;
        } catch (\Illuminate\Database\QueryException $exc) {
            Log::error($exc->getMessage(), $exc->getTrace());
            return false;
        }
    }
}
