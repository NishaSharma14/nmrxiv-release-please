<?php

namespace App\Policies;

use App\Models\Study;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function viewStudy(User $user, Study $study)
    {
        if (is_null($user) && $study->is_public) {
            return true;
        }

        return $user->belongsToStudy($study);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function createStudy(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function updateStudy(User $user, Study $study)
    {
        if ($study->is_public || $study->is_archived || $study->is_deleted) {
            return false;
        }

        return $user->canUpdateStudy($study);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function deleteStudy(User $user, Study $study)
    {
        return $user->isStudyCreator($study);
    }

    /**
     * Determine whether the user can add study members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function addStudyMember(User $user, Study $study)
    {
        return $user->ownsStudy($study);
    }

    /**
     * Determine whether the user can update study member permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function updateStudyMember(User $user, Study $study)
    {
        return $user->ownsStudy($study);
    }

    /**
     * Determine whether the user can remove study members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Study  $study
     * @return mixed
     */
    public function removeStudyMember(User $user, Study $study)
    {
        return $user->ownsStudy($study);
    }
}
