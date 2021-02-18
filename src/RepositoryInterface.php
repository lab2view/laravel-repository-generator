<?php

namespace Lab2view\RepositoryGenerator;

interface RepositoryInterface
{
    /**
     * @param string $key
     * @param $value
     * @param bool $withTrashed
     * @return mixed
     */
    public function exists(string $key, $value, $withTrashed = false);

    /**
     * @param string $attr_name
     * @param $attr_value
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getByAttribute(string $attr_name, $attr_value, $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param int $n
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getPaginate(int $n, $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param array $inputs
     * @return mixed
     */
    public function store(Array $inputs);

    /**
     * @param $id
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getById($id, $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param $id
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     * @return mixed
     */
    public function getByIdOrFail($id, $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param $key
     * @param $value
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function search($key, $value, array $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param array $relations
     * @param bool $withTrashed
     * @param array $selects
     * @return mixed
     */
    public function getAll(array $relations = [], $withTrashed = false, $selects = []);

    /**
     * @param bool $withTrashed
     * @return mixed
     */
    public function countAll($withTrashed = false);

    /**
     * @param $key
     * @return mixed
     */
    public function getAllSelectable($key);

    /**
     * @param $id
     * @param array $inputs
     * @return mixed
     */
    public function update($id, Array $inputs);

    /**
     * @param $id
     * @return bool
     */
    public function destroy($id);

    /**
     * @return bool
     */
    public function destroyAll();

    /**
     * @param $id
     * @return bool
     */
    public function forceDelete($id);

    /**
     * @param $id
     * @return bool
     */
    public function destroyThenForceDelete($id);

    /**
     * @param $id
     * @return bool
     */
    public function restore($id);
}
