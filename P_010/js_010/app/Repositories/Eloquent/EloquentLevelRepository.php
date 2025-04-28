<?php

namespace App\Repositories\Eloquent;

use App\Models\LevelModel;
use App\Repositories\Interfaces\LevelRepositoryInterface;

class EloquentLevelRepository implements LevelRepositoryInterface
{
    protected $level;

    public function __construct(LevelModel $level)
    {
        $this->level = $level;
    }

    public function all()
    {
        return $this->level->all();
    }

    public function find(int $id)
    {
        return $this->level->find($id);
    }

    public function findByKodeOrNama(string $levelKode, string $levelNama)
    {
        return $this->level::where('level_kode', $levelKode)
            ->orWhere('level_nama', $levelNama)
            ->first();
    }

    public function getList(array $filter = [])
    {
        $query = $this->level::select('level_id', 'level_kode', 'level_nama');

        if (!empty($filter['level_id'])) {
            $query->where('level_id', $filter['level_id']);
        }

        return $query;
    }

    public function getAllLevelsOrderedByKode()
    {
        return $this->level::select('level_kode', 'level_nama')
            ->orderBy('level_kode', 'ASC')
            ->get();
    }

    public function create(array $data)
    {
        return $this->level->create($data);
    }

    public function insertMany(array $data)
    {
        return $this->level::insert($data);
    }


    // public function update(int $id, array $data)
    // {
    //     $model = $this->level->find($id);
    //     return $model ? $model->update($data) : false;
    // }

    // public function delete(int $id)
    // {
    //     $model = $this->level->find($id);
    //     return $model ? $model->delete() : false;
    // }
}
