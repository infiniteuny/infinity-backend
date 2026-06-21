import http from "k6/http";
import { uid, IMAGE_BIN } from "../helpers.js";

export function generateAchievementData(ids) {
    const uniqueId = uid();

    return {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_time_range_id: ids.competitionTimeRangeId,
        competition_output_id: ids.competitionOutputId,
        competition_rank_id: ids.competitionRankId,
        competition_branch: `k6_ach_${uniqueId}`,
        competition_start_date: "2025-01-01",
        competition_end_date: "2025-12-31",
        description: `k6_achievement_${uniqueId}`,
        status: "PENDING",
        image: http.file(
            IMAGE_BIN,
            `achievement_${uniqueId}.jpg`,
            "image/jpeg",
        ),
    };
}

export function generateAchievementUpdateData(ids) {
    const uniqueId = uid();

    return {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_time_range_id: ids.competitionTimeRangeId,
        competition_output_id: ids.competitionOutputId,
        competition_rank_id: ids.competitionRankId,
        competition_branch: `k6_ach_upd_${uniqueId}`,
        competition_start_date: "2025-02-01",
        competition_end_date: "2025-11-30",
        description: `k6_achievement_upd_${uniqueId}`,
        status: "PENDING",
    };
}
