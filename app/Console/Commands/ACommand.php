<?php namespace App\Console\Commands;

use App\Logging;
use App\LogTypes;
use Illuminate\Console\Command;

class ACommand extends Command
{

  /**
   * @param string $question
   * @param string|null $default
   * @return string
   */
  protected function askStringQuestion(string $question, string $default = null): string {
    $answer = null;

    while(true) {
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

    while(true) {
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

  /**
   * @param string $function
   * @param string $comment
   * @param string $type
   */
  protected function log(string $function, string $comment, string $type) {
    switch ($type) {
      case LogTypes::INFO:
        Logging::info($function, $comment);
        break;

      case LogTypes::WARNING:
        Logging::warning($function, $comment);
        break;

      default:
        Logging::error('ACommand log', 'Unknown log type!');
        break;
    }

    $this->comment($function . ' | ' . $comment);
  }
}