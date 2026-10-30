<?php

return [

    /*
    | Firebase Admin SDK (push notifications — next phase)
    | Download JSON: Firebase Console → Project settings → Service accounts → Generate new private key
    | Save as storage/app/firebase/firebase-service-account.json (not google-services.json)
    */

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase-service-account.json')),

    'project_id' => env('FIREBASE_PROJECT_ID'),

];
