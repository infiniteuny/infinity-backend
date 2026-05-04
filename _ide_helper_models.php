<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string $id
 * @property string $team_id
 * @property string $competition_scale_id
 * @property string $competition_time_range_id
 * @property string $competition_output_id
 * @property string $competition_rank_id
 * @property string $competition_branch
 * @property \Illuminate\Support\Carbon $competition_start_date
 * @property \Illuminate\Support\Carbon $competition_end_date
 * @property string $description
 * @property mixed $image
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $competition_instance_id
 * @property-read \App\Models\CompetitionInstance $competitionInstance
 * @property-read \App\Models\CompetitionOutput $competitionOutput
 * @property-read \App\Models\CompetitionRank $competitionRank
 * @property-read \App\Models\CompetitionScale $competitionScale
 * @property-read \App\Models\CompetitionTimeRange $competitionTimeRange
 * @property-read mixed $point
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\AchievementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionOutputId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionRankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionScaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCompetitionTimeRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereUpdatedAt($value)
 */
	class Achievement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property int $year
 * @property int $total_points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\AchievementLeaderboardFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereTotalPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementLeaderboard whereYear($value)
 */
	class AchievementLeaderboard extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $priority
 * @property string $description
 * @property mixed $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CommunityGroupMember|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @method static \Database\Factories\CommunityGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroup whereUpdatedAt($value)
 */
	class CommunityGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $year
 * @property string $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_active
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\CommunityGroupAdminMember|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @method static \Database\Factories\CommunityGroupAdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdmin whereYear($value)
 */
	class CommunityGroupAdmin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $community_group_admin_id
 * @property string $community_group_id
 * @property mixed $photo
 * @property mixed|null $animation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CommunityGroup $communityGroup
 * @property-read \App\Models\CommunityGroupAdmin $communityGroupAdmin
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereAnimation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereCommunityGroupAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereCommunityGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupAdminMember whereUserId($value)
 */
	class CommunityGroupAdminMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $community_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CommunityGroup $communityGroup
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember whereCommunityGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityGroupMember whereUserId($value)
 */
	class CommunityGroupMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompetitionInstance> $competitionInstances
 * @property-read int|null $competition_instances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FundApplication> $fundApplications
 * @property-read int|null $fund_applications_count
 * @method static \Database\Factories\CompetitionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Competition whereUpdatedAt($value)
 */
	class Competition extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $competition_id
 * @property string $name
 * @property string $description
 * @property string|null $url
 * @property string $organizer
 * @property string $organizer_type_id
 * @property mixed $logo
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \App\Models\Competition $competition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FundApplication> $fundApplications
 * @property-read int|null $fund_applications_count
 * @property-read \App\Models\CompetitionOrganizerType $organizerType
 * @method static \Database\Factories\CompetitionInstanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereCompetitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereOrganizer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereOrganizerTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionInstance whereUrl($value)
 */
	class CompetitionInstance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompetitionInstance> $competitionInstances
 * @property-read int|null $competition_instances_count
 * @method static \Database\Factories\CompetitionOrganizerTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOrganizerType whereWeight($value)
 */
	class CompetitionOrganizerType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @method static \Database\Factories\CompetitionOutputFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionOutput whereWeight($value)
 */
	class CompetitionOutput extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @method static \Database\Factories\CompetitionRankFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionRank whereWeight($value)
 */
	class CompetitionRank extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FundApplication> $fundApplications
 * @property-read int|null $fund_applications_count
 * @method static \Database\Factories\CompetitionScaleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionScale whereWeight($value)
 */
	class CompetitionScale extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FundApplication> $fundApplications
 * @property-read int|null $fund_applications_count
 * @method static \Database\Factories\CompetitionTeamTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTeamType whereWeight($value)
 */
	class CompetitionTeamType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @method static \Database\Factories\CompetitionTimeRangeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompetitionTimeRange whereWeight($value)
 */
	class CompetitionTimeRange extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $key
 * @property string $value
 * @property string $type
 * @property bool $is_private
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ConfigFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereValue($value)
 */
	class Config extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $year
 * @property string $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_active
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\CoreTeamMember|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @method static \Database\Factories\CoreTeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeam whereYear($value)
 */
	class CoreTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\CoreTeamDivisionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamDivision whereUpdatedAt($value)
 */
	class CoreTeamDivision extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $core_team_id
 * @property string $core_team_division_id
 * @property mixed $photo
 * @property mixed|null $animation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CoreTeam $coreTeam
 * @property-read \App\Models\CoreTeamDivision $coreTeamDivision
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereAnimation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereCoreTeamDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereCoreTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoreTeamMember whereUserId($value)
 */
	class CoreTeamMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Major> $majors
 * @property-read int|null $majors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\DegreeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Degree whereUpdatedAt($value)
 */
	class Degree extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Major> $majors
 * @property-read int|null $majors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\FacultyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faculty whereUpdatedAt($value)
 */
	class Faculty extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $team_id
 * @property string $competition_scale_id
 * @property string $competition_branch
 * @property \Illuminate\Support\Carbon $competition_start_date
 * @property \Illuminate\Support\Carbon $competition_end_date
 * @property mixed $letter_of_acceptance
 * @property mixed $proposal
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $competition_instance_id
 * @property-read \App\Models\CompetitionInstance $competitionInstance
 * @property-read \App\Models\CompetitionScale $competitionScale
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\FundApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCompetitionBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCompetitionEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCompetitionInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCompetitionScaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCompetitionStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereLetterOfAcceptance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereProposal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FundApplication whereUpdatedAt($value)
 */
	class FundApplication extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $sso_id
 * @property string $name
 * @property string $guard_name
 * @property bool $is_managed
 * @property string|null $sso_last_synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserGroup|\App\Models\GroupPermission|null $entitlement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\GroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereIsManaged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereSsoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereSsoLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group withoutPermission($permissions)
 */
	class Group extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $group_id
 * @property string $permission_id
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\Permission $permission
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupPermission wherePermissionId($value)
 */
	class GroupPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $degree_id
 * @property string $faculty_id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Degree $degree
 * @property-read \App\Models\Faculty $faculty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\MajorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereDegreeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereFacultyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Major whereUpdatedAt($value)
 */
	class Major extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserPermission|\App\Models\GroupPermission|null $entitlement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\PermissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission group($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutGroup(\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection|\BackedEnum|array|string|int $roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutRole($roles, ?string $guard = null)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $priority
 * @property string $description
 * @property mixed $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserPersona|null $entitlement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\PersonaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Persona whereUpdatedAt($value)
 */
	class Persona extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $url
 * @property mixed $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ProjectGalleryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectGallery whereUrl($value)
 */
	class ProjectGallery extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $leader_id
 * @property string $name
 * @property bool $is_personal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $team_type_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FundApplication> $fundApplications
 * @property-read int|null $fund_applications_count
 * @property-read \App\Models\User $leader
 * @property-read \App\Models\TeamMember|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\CompetitionTeamType $teamType
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereIsPersonal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLeaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereTeamTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMember whereUserId($value)
 */
	class TeamMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $position
 * @property mixed $photo
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\TestimonialFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereUpdatedAt($value)
 */
	class Testimonial extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $sso_id
 * @property string $user_id
 * @property \Illuminate\Support\Carbon $last_used_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\TokenFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereSsoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token withoutTrashed()
 */
	class Token extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $sso_id
 * @property string $name
 * @property string $username
 * @property string $email_address
 * @property string $phone_number
 * @property string $student_id
 * @property string $major_id
 * @property array<array-key, mixed> $links
 * @property string|null $start_date
 * @property string|null $end_date
 * @property bool $is_member
 * @property bool $is_extraordinary
 * @property string|null $sso_last_synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TeamMember|\App\Models\CoreTeamMember|\App\Models\CommunityGroupMember|\App\Models\CommunityGroupAdminMember|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CommunityGroupAdmin> $communityGroupAdmins
 * @property-read int|null $community_group_admins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CommunityGroup> $communityGroups
 * @property-read int|null $community_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CoreTeam> $coreTeams
 * @property-read int|null $core_teams_count
 * @property-read \App\Models\UserPersona|\App\Models\UserPermission|\App\Models\UserGroup|null $entitlement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read mixed $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ledTeams
 * @property-read int|null $led_teams_count
 * @property-read \App\Models\Major $major
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Persona> $personas
 * @property-read int|null $personas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User group($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsExtraordinary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMajorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSsoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSsoLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutGroup(\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection|\BackedEnum|array|string|int $roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\Authenticatable, \Illuminate\Contracts\Auth\Access\Authorizable, \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $group_id
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGroup whereUserId($value)
 */
	class UserGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $permission_id
 * @property-read \App\Models\Permission $permission
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPermission whereUserId($value)
 */
	class UserPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $persona_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Persona $persona
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona wherePersonaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPersona whereUserId($value)
 */
	class UserPersona extends \Eloquent {}
}

