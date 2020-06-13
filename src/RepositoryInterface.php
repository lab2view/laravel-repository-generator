<?php

namespace Lab2view\RepositoryGenerator;

interface RepositoryInterface
{
    /**
     * @param int $n
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getPaginate(int $n, $relations = [], $withTrashed = false);

    /**
     * @param array $inputs
     * @return mixed
     */
    public function store(Array $inputs);

    /**
     * @param $id
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getById($id, $relations = [], $withTrashed = false);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function search($key, $value);

    /**
     * @param array $relations
     * @param bool $withTrashed
     * @return mixed
     */
    public function getAll(array $relations, $withTrashed = false);

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
    public function restore($id);
}
