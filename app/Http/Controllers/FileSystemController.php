<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\FileSystemObject;
use App\Models\Project;
use App\Models\Study;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileSystemController extends Controller
{
    /**
     * Create a new draft signed URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signedDraftStorageURL(Request $request)
    {
        $filePath = null;

        $files = $request->get('draft_files');

        $filesURLs = [];

        foreach ($files as $file) {
            $relativefilePath = null;

            DB::transaction(function () use ($request, &$filePath, $file, &$relativefilePath) {
                $destination = $request->get('destination');

                $draft = Draft::find($request->get('draft_id'));

                $path = null;

                if (array_key_exists('fullPath', $file)) {
                    $path = $file['fullPath'];
                }

                $hasDirectories = $path || $destination != '/' ? true : false;

                $filename = '/'.$file['upload']['filename'];

                $user = $request->user();

                $level = 0;

                $currentLevel = $level;

                $relativefilePath = $path ? $path : $filename;

                $relativefilePath = $destination.'/'.$relativefilePath;

                $path = $destination.'/'.$path;

                $environment = env('APP_ENV', 'local');

                $filePath = preg_replace(
                    '~//+~',
                    '/',
                    '/'.
                    $draft->path.
                    '/'
                    .
                    $relativefilePath
                );

                if ($hasDirectories) {
                    $directories = array_values(
                        array_filter(
                            explode('/', str_replace($filename, '', $path))
                        )
                    );
                    if ($level + count($directories) - 1 > $level) {
                        for (
                            $currentLevel;
                            $currentLevel < $level + count($directories) - 1;
                            $currentLevel++
                        ) {
                            $dPath = implode(
                                '/',
                                array_slice($directories, 0, $currentLevel)
                            );
                            $rURL = rtrim(
                                preg_replace(
                                    '~//+~',
                                    '/',
                                    '/'.
                                        $dPath.
                                        '/'.
                                        $directories[$currentLevel]
                                ),
                                '/'
                            );
                            $parentFileSystemObject = FileSystemObject::firstOrCreate(
                                [
                                    'name' => $directories[$currentLevel],
                                    'slug' => Str::slug(
                                        $directories[$currentLevel],
                                        '-'
                                    ),
                                    'description' => $directories[$currentLevel],
                                    'relative_url' => $rURL,
                                    'path' => preg_replace(
                                        '~//+~',
                                        '/',
                                        '/'.
                                        $draft->path.
                                        '/'
                                        .
                                        $rURL
                                    ),
                                    'type' => 'directory',
                                    'key' => $directories[$currentLevel],
                                    'is_root' => $currentLevel == 0 ? 1 : 0,
                                    'draft_id' => $draft->id,
                                    'level' => $currentLevel,
                                ], [
                                    'uuid' => Str::uuid(),
                                ]
                            );

                            $dPath = implode(
                                '/',
                                array_slice($directories, 0, $currentLevel + 1)
                            );
                            $rURL = rtrim(
                                preg_replace(
                                    '~//+~',
                                    '/',
                                    '/'.
                                        $dPath.
                                        '/'.
                                        $directories[$currentLevel + 1]
                                ),
                                '/'
                            );
                            $childFileSystemObject = FileSystemObject::firstOrCreate(
                                [
                                    'name' => $directories[$currentLevel + 1],
                                    'slug' => Str::slug(
                                        $directories[$currentLevel + 1],
                                        '-'
                                    ),
                                    'description' => $directories[$currentLevel + 1],
                                    'relative_url' => $rURL,
                                    'path' => preg_replace(
                                        '~//+~',
                                        '/',
                                        '/'.
                                        $draft->path.
                                        '/'
                                        .
                                        $rURL
                                    ),
                                    'type' => 'directory',
                                    'key' => $directories[$currentLevel + 1],
                                    'is_root' => $currentLevel + 1 == 0 ? 1 : 0,
                                    'draft_id' => $draft->id,
                                    'level' => $currentLevel + 1,
                                ], [
                                    'uuid' => Str::uuid(),
                                ]
                            );
                            if (! $childFileSystemObject->parent_id) {
                                $childFileSystemObject->parent_id =
                                    $parentFileSystemObject->id;
                                $childFileSystemObject->save();
                                $parentFileSystemObject->has_children = 1;
                                $parentFileSystemObject->save();
                            }
                        }
                    } else {
                        $dPath = implode(
                            '/',
                            array_slice($directories, 0, $currentLevel)
                        );
                        $rURL = rtrim(
                            preg_replace(
                                '~//+~',
                                '/',
                                '/'.$dPath.'/'.$directories[$currentLevel]
                            ),
                            '/'
                        );
                        $childFileSystemObject = FileSystemObject::firstOrCreate([
                            'name' => $directories[$currentLevel],
                            'slug' => Str::slug($directories[$currentLevel], '-'),
                            'description' => $directories[$currentLevel],
                            'relative_url' => $rURL,
                            'path' => preg_replace(
                                '~//+~',
                                '/',
                                '/'.
                                $draft->path.
                                '/'
                                .
                                $rURL
                            ),
                            'type' => 'directory',
                            'key' => $directories[$currentLevel],
                            'is_root' => $currentLevel == 0 ? 1 : 0,
                            'draft_id' => $draft->id,
                            'level' => $currentLevel,
                        ], [
                            'uuid' => Str::uuid(),
                        ]);
                    }
                }

                if ($hasDirectories) {
                    $childFileSystemObject->has_children = 1;
                    $childFileSystemObject->save();
                }

                $filename = preg_replace(
                    '~/~',
                    '',
                    $filename
                );

                $fileFileSystemObject = FileSystemObject::firstOrCreate([
                    'name' => $filename,
                    'slug' => Str::slug($filename, '-'),
                    'description' => $filename,
                    'relative_url' => rtrim(
                        preg_replace('~//+~', '/', $relativefilePath),
                        '/'
                    ),
                    'path' => $filePath,
                    'type' => 'file',
                    'key' => $filename,
                    'is_root' => 0,
                    'draft_id' => $draft->id,
                    'level' => $hasDirectories ? $currentLevel + 1 : $currentLevel,
                    'parent_id' => $hasDirectories
                        ? $childFileSystemObject->id
                        : null,
                ], [
                    'uuid' => Str::uuid(),
                    'info' => json_encode(
                        [
                            'size' => $file['upload']['total'],
                        ]
                    ),
                ]);
            }, 5);

            $bucket =
                $request->input('bucket') ?:
                config('filesystems.disks.minio.bucket');

            $client = $this->storageClient();

            $uuid = (string) Str::uuid();

            $signedRequest = $client->createPresignedRequest(
                $this->createCommand($request, $client, $bucket, $key = $filePath),
                '+90 minutes'
            );

            array_push($filesURLs, [
                'uuid' => $uuid,
                'bucket' => $bucket,
                'key' => $key,
                'fullPath' => array_key_exists('fullPath', $file) ? $file['fullPath'] : preg_replace(
                    '~//+~',
                    '/', $relativefilePath),
                'url' => (string) $signedRequest->getUri(),
                'headers' => $this->headers($request, $signedRequest),
            ]);
        }

        return response()->json(
            $filesURLs,
            201
        );
    }

    /**
     * Create a new signed URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signedStorageURL(Request $request)
    {
        $filePath = null;

        DB::transaction(function () use ($request, &$filePath) {
            $file = $request->get('file');

            $destination = $request->get('destination');

            $project = Project::find($request->get('project_id'));

            $study = Study::find($request->get('study_id'));

            $path = null;

            if (array_key_exists('fullPath', $file)) {
                $path = $file['fullPath'];
            }

            $hasDirectories = $path || $destination != '/' ? true : false;

            $filename = '/'.$file['upload']['filename'];

            $user = $request->user();

            $level = 0;

            $currentLevel = $level;

            $relativefilePath = $path ? $path : $filename;

            $relativefilePath = $destination.'/'.$relativefilePath;
            $path = $destination.'/'.$path;

            $environment = env('APP_ENV', 'local');

            $filePath = preg_replace(
                '~//+~',
                '/',
                $environment.
                    '/'.
                    $project->uuid.
                    '/'.
                    $study->uuid.
                    $relativefilePath
            );

            if ($hasDirectories) {
                $directories = array_values(
                    array_filter(
                        explode('/', str_replace($filename, '', $path))
                    )
                );
                if ($level + count($directories) - 1 > $level) {
                    for (
                        $currentLevel;
                        $currentLevel < $level + count($directories) - 1;
                        $currentLevel++
                    ) {
                        $dPath = implode(
                            '/',
                            array_slice($directories, 0, $currentLevel)
                        );
                        $parentFileSystemObject = FileSystemObject::firstOrCreate(
                            [
                                'name' => $directories[$currentLevel],
                                'slug' => Str::slug(
                                    $directories[$currentLevel],
                                    '-'
                                ),
                                'description' => $directories[$currentLevel],
                                'relative_url' => rtrim(
                                    preg_replace(
                                        '~//+~',
                                        '/',
                                        '/'.
                                            $dPath.
                                            '/'.
                                            $directories[$currentLevel]
                                    ),
                                    '/'
                                ),
                                'type' => 'directory',
                                'key' => $directories[$currentLevel],
                                'is_root' => $currentLevel == 0 ? 1 : 0,
                                'project_id' => $project->id,
                                'study_id' => $study->id,
                                'level' => $currentLevel,
                            ], [
                                'uuid' => Str::uuid(),
                            ]
                        );

                        $dPath = implode(
                            '/',
                            array_slice($directories, 0, $currentLevel + 1)
                        );
                        $childFileSystemObject = FileSystemObject::firstOrCreate(
                            [
                                'name' => $directories[$currentLevel + 1],
                                'slug' => Str::slug(
                                    $directories[$currentLevel + 1],
                                    '-'
                                ),
                                'description' => $directories[$currentLevel + 1],
                                'relative_url' => rtrim(
                                    preg_replace(
                                        '~//+~',
                                        '/',
                                        '/'.
                                            $dPath.
                                            '/'.
                                            $directories[$currentLevel + 1]
                                    ),
                                    '/'
                                ),
                                'type' => 'directory',
                                'key' => $directories[$currentLevel + 1],
                                'is_root' => $currentLevel + 1 == 0 ? 1 : 0,
                                'project_id' => $project->id,
                                'study_id' => $study->id,
                                'level' => $currentLevel + 1,
                            ], [
                                'uuid' => Str::uuid(),
                            ]
                        );
                        if (! $childFileSystemObject->parent_id) {
                            $childFileSystemObject->parent_id =
                                $parentFileSystemObject->id;
                            $childFileSystemObject->save();
                            $parentFileSystemObject->has_children = 1;
                            $parentFileSystemObject->save();
                        }
                    }
                } else {
                    $dPath = implode(
                        '/',
                        array_slice($directories, 0, $currentLevel)
                    );
                    $childFileSystemObject = FileSystemObject::firstOrCreate([
                        'name' => $directories[$currentLevel],
                        'slug' => Str::slug($directories[$currentLevel], '-'),
                        'description' => $directories[$currentLevel],
                        'relative_url' => rtrim(
                            preg_replace(
                                '~//+~',
                                '/',
                                '/'.$dPath.'/'.$directories[$currentLevel]
                            ),
                            '/'
                        ),
                        'type' => 'directory',
                        'key' => $directories[$currentLevel],
                        'is_root' => $currentLevel == 0 ? 1 : 0,
                        'project_id' => $request->get('project_id'),
                        'study_id' => $request->get('study_id'),
                        'level' => $currentLevel,
                    ], [
                        'uuid' => Str::uuid(),
                    ]);
                }
            }

            if ($hasDirectories) {
                $childFileSystemObject->has_children = 1;
                $childFileSystemObject->save();
            }

            $filename = preg_replace(
                '~/~',
                '',
                $filename
            );

            $fileFileSystemObject = FileSystemObject::firstOrCreate([
                'name' => $filename,
                'slug' => Str::slug($filename, '-'),
                'description' => $filename,
                'relative_url' => rtrim(
                    preg_replace('~//+~', '/', $relativefilePath),
                    '/'
                ),
                'type' => 'file',
                'key' => $filename,
                'is_root' => 0,
                'project_id' => $request->get('project_id'),
                'study_id' => $request->get('study_id'),
                'level' => $hasDirectories ? $currentLevel + 1 : $currentLevel,
                'parent_id' => $hasDirectories
                    ? $childFileSystemObject->id
                    : null,
            ], [
                'uuid' => Str::uuid(),
            ]);
        }, 5);

        $bucket =
            $request->input('bucket') ?:
            config('filesystems.disks.minio.bucket');

        $client = $this->storageClient();

        $uuid = (string) Str::uuid();

        $signedRequest = $client->createPresignedRequest(
            $this->createCommand($request, $client, $bucket, $key = $filePath),
            '+90 minutes'
        );

        return response()->json(
            [
                'uuid' => $uuid,
                'bucket' => $bucket,
                'key' => $key,
                'url' => (string) $signedRequest->getUri(),
                'headers' => $this->headers($request, $signedRequest),
            ],
            201
        );
    }

    /**
     * Create a command for the PUT operation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Aws\S3\S3Client  $client
     * @param  string  $bucket
     * @param  string  $key
     * @return \Aws\Command
     */
    protected function createCommand(
        Request $request,
        S3Client $client,
        $bucket,
        $key
    ) {
        return $client->getCommand(
            'putObject',
            array_filter([
                'Bucket' => $bucket,
                'Key' => $key,
            ])
        );
    }

    /**
     * Get the headers that should be used when making the signed request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GuzzleHttp\Psr7\Request
     * @return array
     */
    protected function headers(Request $request, $signedRequest)
    {
        return array_merge($signedRequest->getHeaders(), [
            'Content-Type' => $request->input('content_type') ?: 'application/octet-stream',
        ]);
    }

    /**
     * Get the S3 storage client instance.
     *
     * @return \Aws\S3\S3Client
     */
    protected function storageClient()
    {
        $config = [
            'region' => config('filesystems.disks.minio.region'),
            'version' => 'latest',
            'use_path_style_endpoint' => true,
            'url' => config('filesystems.disks.minio.endpoint'),
            'endpoint' => config('filesystems.disks.minio.endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.minio.key'),
                'secret' => config('filesystems.disks.minio.secret'),
            ],
        ];

        return S3Client::factory($config);
    }
}
