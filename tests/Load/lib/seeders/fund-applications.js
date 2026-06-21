import http from "k6/http";
import { uid, PDF_BIN, DOCX_BIN } from "../helpers.js";

export function generateFundApplicationData(ids) {
    const uniqueId = uid();

    return {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_branch: `k6_fab_${uniqueId}`,
        competition_start_date: "2025-01-01",
        competition_end_date: "2025-12-31",
        status: "PENDING",
        letter_of_acceptance: http.file(
            PDF_BIN,
            `loa_${uniqueId}.pdf`,
            "application/pdf",
        ),
        proposal: http.file(
            DOCX_BIN,
            `proposal_${uniqueId}.docx`,
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        ),
    };
}

export function generateFundApplicationUpdateData(ids) {
    const uniqueId = uid();

    return {
        team_id: ids.teamId,
        competition_instance_id: ids.competitionInstanceId,
        competition_scale_id: ids.competitionScaleId,
        competition_branch: `k6_fab_upd_${uniqueId}`,
        competition_start_date: "2025-02-01",
        competition_end_date: "2025-11-30",
        status: "PENDING",
    };
}
