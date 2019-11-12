<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;

interface IMovieYearRepository
{
  public function getMovieYearById(int $id);

  public function checkIfMovieYearExistsById(int $id): bool;

  public function getMovieYearsOrderedByDate();

  public function createMovieYear(int $year);

  public function updateMovieYear(MovieYear $movieYear, int $year);

  public function deleteMovieYear(MovieYear $movieYear): bool;
}