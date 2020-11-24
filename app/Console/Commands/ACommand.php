<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class ACommand extends Command {
  public function tets() {
    echo 'test';
  }
  /**
   * @param string $question
   * @param string|null $default
   * @return string
   */
  protected function askStringQuestion(string $question, string $default = null): string {
    $answer = null;

    while (true) {
      if ($default != null) {
        $answer = $this->ask($question . ' [' . $default . ']');
      } else {
        $answer = $this->ask($question);
      }

      if ($answer == null) {
        $answer = $default;
      }

      $this->comment('Your answer was: [' . $answer . ']');

      $check = $this->anticipate('Is this correct? [Y/n]', ['y', 'n']);
      if ($check == null) {
        break;
      } else {
        if ($check == 'y') {
          break;
        }
      }
    }

    return $answer;
  }

  /**
   * @param string $question
   * @return bool
   */
  protected function askBooleanQuestion(string $question): bool {
    $answer = null;

    while (true) {
      $answer = $this->anticipate($question . ' [Y/n]', ['y', 'n']);
      if ($answer == null) {
        $answer = true;
      } else {
        if ($answer == 'y') {
          $answer = true;
        } else {
          $answer = false;
        }
      }

      if ($answer) {
        $stringAnswer = 'true';
      } else {
        $stringAnswer = 'false';
      }

      $this->comment('Your answer was: [' . $stringAnswer . ']');

      $check = $this->anticipate('Is this correct? [Y/n]', ['y', 'n']);
      if ($check == null) {
        break;
      } else {
        if ($check == 'y') {
          break;
        }
      }
    }

    return $answer;
  }
}
