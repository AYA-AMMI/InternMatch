<?php

namespace App\Service;

use App\Entity\Student;
use App\Entity\Internship;

class MatchingService
{
    
    public function calculateMatchScore(Student $student, Internship $internship): int
    {
        $score = 0;

        // 1. Skills matching (50% of total score)
        $score += $this->calculateSkillsScore($student, $internship);

        // 2. Location matching (25% of total score)
        $score += $this->calculateLocationScore($student, $internship);

        // 3. Duration matching (15% of total score)
        $score += $this->calculateDurationScore($student, $internship);

        // 4. Industry preference (10% of total score)
        $score += $this->calculateIndustryScore($student, $internship);

        return min((int)$score, 100); // Cap at 100
    }

    /**
     * Calculate skills match score (max 50 points)
     */
    private function calculateSkillsScore(Student $student, Internship $internship): int
    {
        $studentSkills = $student->getSkills() ?? [];
        $requiredSkills = $internship->getRequiredSkills() ?? [];

        if (empty($requiredSkills)) {
            return 25; // Give partial score if no specific skills required
        }

        // Normalize to lowercase for comparison
        $studentSkillsLower = array_map('strtolower', $studentSkills);
        $requiredSkillsLower = array_map('strtolower', $requiredSkills);

        // Find common skills
        $commonSkills = array_intersect($studentSkillsLower, $requiredSkillsLower);
        
        // Calculate percentage match
        $matchPercentage = count($commonSkills) / count($requiredSkillsLower);
        
        return (int)($matchPercentage * 50);
    }

    /**
     * Calculate location match score (max 25 points)
     */
    private function calculateLocationScore(Student $student, Internship $internship): int
    {
        $studentLocation = strtolower(trim($student->getExpectedLocation() ?? ''));
        $internshipLocation = strtolower(trim($internship->getLocation() ?? ''));

        if (empty($studentLocation) || empty($internshipLocation)) {
            return 10; // Default score if location not specified
        }

        // Exact match
        if ($studentLocation === $internshipLocation) {
            return 25;
        }

        // Partial match (same city or region)
        if (str_contains($internshipLocation, $studentLocation) || 
            str_contains($studentLocation, $internshipLocation)) {
            return 15;
        }

        // No match
        return 0;
    }

    /**
     * Calculate duration match score (max 15 points)
     */
    private function calculateDurationScore(Student $student, Internship $internship): int
    {
        $studentDuration = $student->getExpectedDuration();
        $internshipDuration = $internship->getDuration();

        if (!$studentDuration || !$internshipDuration) {
            return 7; // Default score
        }

        $difference = abs($studentDuration - $internshipDuration);

        // Exact match
        if ($difference === 0) {
            return 15;
        }

        // ±1 month difference
        if ($difference === 1) {
            return 10;
        }

        // ±2 months difference
        if ($difference === 2) {
            return 5;
        }

        // More than 2 months difference
        return 0;
    }

    /**
     * Calculate industry preference score (max 10 points)
     */
    private function calculateIndustryScore(Student $student, Internship $internship): int
    {
        // Check if student has previously applied to this industry
        $hasAppliedToIndustry = false;
        
        foreach ($student->getApplications() as $application) {
            if ($application->getInternship()->getCompany()->getIndustry() === $internship->getCompany()->getIndustry()) {
                $hasAppliedToIndustry = true;
                break;
            }
        }

        // If previously applied to this industry, give full points
        if ($hasAppliedToIndustry) {
            return 10;
        }

        // Otherwise, give partial points for openness
        return 5;
    }

    /**
     * Get match quality label based on score
     */
    public function getMatchLabel(int $score): string
    {
        if ($score >= 90) {
            return 'Excellent Match';
        } elseif ($score >= 75) {
            return 'Good Match';
        } elseif ($score >= 60) {
            return 'Fair Match';
        } else {
            return 'Low Match';
        }
    }

    /**
     * Get match badge color based on score
     */
    public function getMatchColor(int $score): string
    {
        if ($score >= 90) {
            return 'success';
        } elseif ($score >= 75) {
            return 'info';
        } elseif ($score >= 60) {
            return 'warning';
        } else {
            return 'secondary';
        }
    }
}