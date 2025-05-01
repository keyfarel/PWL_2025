<?php

namespace App\Repositories\Interfaces;

interface LevelRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByKodeOrNama(string $levelKode, string $levelNama);
    public function getList(array $filter = []);
    public function getAllLevelsOrderedByKode();
    public function create(array $data);
    public function insertMany(array $data);

    // public function update(int $id, array $data);
    // public function delete(int $id);
}
