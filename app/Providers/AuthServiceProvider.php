<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Achievement;
use App\Models\Competition;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionTeamType;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Major;
use App\Models\Post;
use App\Models\ProjectGallery;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTimeRange;
use App\Models\Config;
use App\Models\Faculty;
use App\Models\FundApplication;
use App\Models\Team;
use App\Models\User;
use App\Policies\AchievementPolicy;
use App\Policies\CompetitionOutputPolicy;
use App\Policies\CompetitionRankPolicy;
use App\Policies\CompetitionScalePolicy;
use App\Policies\CompetitionTimeRangePolicy;
use App\Policies\ConfigPolicy;
use App\Policies\FacultyPolicy;
use App\Policies\FundApplicationPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\Policies\CompetitionPolicy;
use App\Policies\CompetitionOrganizerTypePolicy;
use App\Policies\CompetitionTeamTypePolicy;
use App\Policies\CoreTeamPolicy;
use App\Policies\CoreTeamDivisionPolicy;
use App\Policies\CoreTeamMemberPolicy;
use App\Policies\DegreePolicy;
use App\Policies\MajorPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProjectGalleryPolicy;
use App\Policies\TeamMemberPolicy;
use App\Policies\TestimonialPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Achievement::class => AchievementPolicy::class,
        CompetitionOutput::class => CompetitionOutputPolicy::class,
        CompetitionRank::class => CompetitionRankPolicy::class,
        CompetitionScale::class => CompetitionScalePolicy::class,
        CompetitionTimeRange::class => CompetitionTimeRangePolicy::class,
        Config::class => ConfigPolicy::class,
        Faculty::class => FacultyPolicy::class,
        FundApplication::class => FundApplicationPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        Competition::class => CompetitionPolicy::class,
        CompetitionOrganizerType::class => CompetitionOrganizerTypePolicy::class,
        CompetitionTeamType::class => CompetitionTeamTypePolicy::class,
        CoreTeam::class => CoreTeamPolicy::class,
        CoreTeamDivision::class => CoreTeamDivisionPolicy::class,
        CoreTeamMember::class => CoreTeamMemberPolicy::class,
        Degree::class => DegreePolicy::class,
        Major::class => MajorPolicy::class,
        Post::class => PostPolicy::class,
        ProjectGallery::class => ProjectGalleryPolicy::class,
        TeamMember::class => TeamMemberPolicy::class,
        Testimonial::class => TestimonialPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
