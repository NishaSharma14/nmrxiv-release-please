<?php

namespace App\Actions\Project;

use App\Models\Project;

class DeleteProject
{
    /**
     * Delete the given project.
     *
     * @param  mixed  $project
     * @return void
     */
    public function delete($project)
    {
        if ($project->is_public) {
            $project->studies()->update(['is_archived' => true]);
            foreach ($project->studies as $study) {
                $study->datasets()->update(['is_archived' => true]);
            }
            $project->is_archived = true;
            $project->save();
        } else {
            $project->studies()->update(['is_deleted' => true]);
            foreach ($project->studies as $study) {
                $study->datasets()->update(['is_deleted' => true]);
            }
            $project->is_deleted = true;
            $project->save();
        }
    }
}
