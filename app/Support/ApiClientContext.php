<?php

namespace App\Support;

use Illuminate\Http\Request;

final class ApiClientContext
{
    public static function isMobileApp(?Request $request): bool
    {
      if (! $request) {
          return false;
      }

      $client = strtolower(trim((string) $request->header('X-Client', '')));
      if (in_array($client, ['mobile', 'mobile-app', 'android', 'ios', 'flutter', 'app'], true)) {
          return true;
      }

      $platform = strtolower(trim((string) $request->header('X-App-Platform', '')));

      return in_array($platform, ['android', 'ios', 'flutter', 'mobile'], true);
  }
}