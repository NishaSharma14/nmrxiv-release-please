<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudyResource extends JsonResource
{
    private bool $lite = true;

    private array $properties = ['sample', 'users', 'license'];

    public function lite(bool $lite, array $properties): self
    {
        $this->lite = $lite;
        $this->properties = $properties;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'team' => $this->when(! $this->team->personal_team, $this->team),
            'photo_url' => $this->study_photo_path,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'is_public' => $this->is_public,
            'updated_at' => $this->updated_at,
            'study_preview_urls' => $this->study_preview_urls,
            $this->mergeWhen(! $this->lite, function () {
                return [
                    $this->mergeWhen(
                        in_array('owner', $this->properties),
                        function () {
                            return [
                                'owner' => new UserResource($this->owner),
                            ];
                        }
                    ),
                ];
            }),
            $this->mergeWhen(! $this->lite, function () {
                return [
                    $this->mergeWhen(
                        in_array('sample', $this->properties),
                        function () {
                            return [
                                'sample' => new SampleResource($this->sample),
                            ];
                        }
                    ),
                ];
            }),
            $this->mergeWhen(! $this->lite, function () {
                return [
                    $this->mergeWhen(
                        in_array('users', $this->properties),
                        function () {
                            return [
                                'users' => UserResource::collection($this->allUsers()),
                            ];
                        }
                    ),
                ];
            }),
            $this->mergeWhen(! $this->lite, function () {
                return [
                    $this->mergeWhen(
                        in_array('datasets', $this->properties),
                        function () {
                            return [
                                'datasets' => DatasetResource::collection($this->datasets),
                            ];
                        }
                    ),
                ];
            }),
            $this->mergeWhen(! $this->lite, function () {
                return [
                    $this->mergeWhen(
                        in_array('license', $this->properties),
                        function () {
                            return [
                                'license' => new LicenseResource($this->license),
                            ];
                        }
                    ),
                ];
            }),
        ];
    }
}
