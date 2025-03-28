<?php

namespace Imtaxu\LaravelLicense\Exceptions;

use Exception;

class LicenseException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        // Report the license exception (e.g., to log file)
        return false;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'License Error',
                'message' => $this->getMessage(),
            ], 403);
        }

        return redirect()->route('license.error');
    }
}
