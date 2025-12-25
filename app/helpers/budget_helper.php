<?php
/**
 * Budget Helper Functions
 * 
 * Utility functions for budget calculations and formatting
 * Used by: API, Dashboard, Reports
 * 
 * Date: 2025-10-25
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

// =====================================================================
// CURRENCY & FORMATTING
// =====================================================================

/**
 * Format amount as currency (SAR)
 * 
 * @param float $amount
 * @param bool $show_currency
 * @return string e.g., "50,000" or "50,000 SAR"
 */
function format_currency($amount, $show_currency = true) {
    $formatted = number_format($amount, 0, '.', ',');
    return $show_currency ? $formatted . ' SAR' : $formatted;
}

/**
 * Format percentage
 * 
 * @param float $percentage
 * @param int $decimals
 * @return string e.g., "35.5%"
 */
function format_percentage($percentage, $decimals = 1) {
    return number_format($percentage, $decimals, '.', '') . '%';
}

/**
 * Format date for display
 * 
 * @param string $date YYYY-MM-DD or YYYY-MM
 * @param string $format default: 'M d, Y'
 * @return string
 */
function format_date_display($date, $format = 'M d, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// =====================================================================
// BUDGET CALCULATIONS
// =====================================================================

/**
 * Calculate percentage used
 * 
 * @param float $spent
 * @param float $allocated
 * @return float 0-100+
 */
function calculate_percentage_used($spent, $allocated) {
    if ($allocated <= 0) return 0;
    return ($spent / $allocated) * 100;
}

/**
 * Calculate remaining amount
 * 
 * @param float $allocated
 * @param float $spent
 * @return float
 */
function calculate_remaining($allocated, $spent) {
    return max(0, $allocated - $spent);
}

/**
 * Get budget status based on percentage used
 * 
 * @param float $percentage_used
 * @return string 'safe' | 'warning' | 'danger' | 'exceeded'
 */
function get_budget_status($percentage_used) {
    if ($percentage_used > 100) {
        return 'exceeded';
    } elseif ($percentage_used >= 80) {
        return 'danger';
    } elseif ($percentage_used >= 50) {
        return 'warning';
    }
    return 'safe';
}

/**
 * Get status badge color
 * 
 * @param string $status
 * @return string CSS class or hex color
 */
function get_status_color($status) {
    $colors = [
        'safe' => '#10B981',      // Green
        'warning' => '#F59E0B',   // Amber/Yellow
        'danger' => '#FB923C',    // Orange
        'exceeded' => '#EF4444',  // Red
        'critical' => '#991B1B'   // Dark Red
    ];
    
    return $colors[$status] ?? '#9CA3AF'; // Gray default
}

/**
 * Get status badge CSS class
 * 
 * @param string $status
 * @return string Tailwind class
 */
function get_status_badge_class($status) {
    $classes = [
        'safe' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-orange-100 text-orange-800',
        'exceeded' => 'bg-red-100 text-red-800',
        'critical' => 'bg-red-900 text-white'
    ];
    
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

// =====================================================================
// TREND CALCULATIONS
// =====================================================================

/**
 * Calculate percentage change
 * 
 * @param float $current
 * @param float $previous
 * @return float e.g., 5.5 for +5.5%
 */
function calculate_trend($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return (($current - $previous) / abs($previous)) * 100;
}

/**
 * Get trend arrow and color
 * 
 * @param float $trend_percentage
 * @param bool $reverse (higher is worse?)
 * @return array ['arrow' => '↑', 'color' => 'green', 'class' => '...']
 */
function get_trend_indicator($trend_percentage, $reverse = false) {
    $is_positive = $trend_percentage >= 0;
    
    if ($reverse) {
        // For spending: higher trend is worse
        $arrow = $is_positive ? '↑' : '↓';
        $color = $is_positive ? 'red' : 'green';
    } else {
        // For savings: higher trend is better
        $arrow = $is_positive ? '↑' : '↓';
        $color = $is_positive ? 'green' : 'red';
    }
    
    return [
        'arrow' => $arrow,
        'color' => $color,
        'class' => 'text-' . $color . '-600',
        'trend' => format_percentage(abs($trend_percentage))
    ];
}

// =====================================================================
// FORECAST CALCULATIONS
// =====================================================================

/**
 * Calculate daily burn rate
 * 
 * @param float $current_spent
 * @param int $days_used
 * @return float Daily average
 */
function calculate_daily_burn_rate($current_spent, $days_used) {
    if ($days_used <= 0) return 0;
    return $current_spent / $days_used;
}

/**
 * Calculate weekly burn rate
 * 
 * @param float $current_spent
 * @param int $days_used
 * @return float Weekly average
 */
function calculate_weekly_burn_rate($current_spent, $days_used) {
    if ($days_used <= 0) return 0;
    $weeks_used = $days_used / 7;
    if ($weeks_used <= 0) return 0;
    return $current_spent / $weeks_used;
}

/**
 * Project end of month spending
 * 
 * @param float $current_spent
 * @param int $days_used
 * @param int $days_remaining
 * @return float Projected total at month end
 */
function project_end_of_month($current_spent, $days_used, $days_remaining) {
    $daily_rate = calculate_daily_burn_rate($current_spent, $days_used);
    return $current_spent + ($daily_rate * $days_remaining);
}

/**
 * Get risk level based on projection vs budget
 * 
 * @param float $projected_end
 * @param float $allocated
 * @return string 'low' | 'medium' | 'high' | 'critical'
 */
function get_risk_level($projected_end, $allocated) {
    if ($allocated <= 0) return 'critical';
    
    $variance_percentage = (($projected_end - $allocated) / $allocated) * 100;
    
    if ($variance_percentage > 20) {
        return 'critical';
    } elseif ($variance_percentage > 10) {
        return 'high';
    } elseif ($variance_percentage > 0) {
        return 'medium';
    }
    return 'low';
}

/**
 * Generate forecast recommendation text
 * 
 * @param float $projected_end
 * @param float $allocated
 * @param float $burn_rate_daily
 * @param int $days_remaining
 * @return string Human-readable recommendation
 */
function generate_forecast_recommendation($projected_end, $allocated, $burn_rate_daily, $days_remaining) {
    $variance = $projected_end - $allocated;
    $daily_reduction_needed = $variance / max(1, $days_remaining);
    
    if ($projected_end <= $allocated) {
        $headroom = $allocated - $projected_end;
        return "On track. You can allocate additional " . format_currency($headroom) . 
               " at current pace.";
    } elseif ($variance > 0 && $variance < ($allocated * 0.1)) {
        // Will exceed by less than 10%
        $reduction_percent = ($daily_reduction_needed / $burn_rate_daily) * 100;
        return "Projected to exceed by " . format_currency($variance) . ". " .
               "Reduce daily spending by " . round($reduction_percent) . "% to stay within budget.";
    } else {
        // Will exceed significantly
        $reduction_percent = ($daily_reduction_needed / $burn_rate_daily) * 100;
        return "WARNING: Projected to exceed by " . format_currency($variance) . ". " .
               "Need to reduce daily spending by " . round($reduction_percent) . "% immediately.";
    }
}

/**
 * Calculate confidence score for forecast
 * 
 * @param int $days_used
 * @param float $variance
 * @param int $data_points
 * @return int 0-100 confidence
 */
function calculate_forecast_confidence($days_used, $variance = 0, $data_points = 0) {
    // Base confidence from days of data (more days = higher confidence)
    $days_confidence = min(100, ($days_used / 5) * 100);
    
    // Adjust for variance (lower variance = higher confidence)
    $variance_penalty = min(20, $variance * 2);
    
    $confidence = $days_confidence - $variance_penalty;
    
    return max(0, min(100, $confidence));
}

// =====================================================================
// PERIOD CALCULATIONS
// =====================================================================

/**
 * Get days used in current month
 * 
 * @param string $period YYYY-MM or null for current
 * @return int
 */
function get_days_used_in_period($period = null) {
    if (!$period) {
        $period = date('Y-m');
    }
    
    $start_date = strtotime($period . '-01');
    $current_date = strtotime(date('Y-m-d'));
    
    // If we're past the month, days used is all days in month
    $year_month = date('Y-m', $current_date);
    if ($year_month > $period) {
        return cal_days_in_month(CAL_GREGORIAN, (int)substr($period, 5, 2), (int)substr($period, 0, 4));
    }
    
    // Count days from start to today
    return (int)(($current_date - $start_date) / 86400) + 1;
}

/**
 * Get days remaining in period
 * 
 * @param string $period YYYY-MM
 * @return int
 */
function get_days_remaining_in_period($period = null) {
    if (!$period) {
        $period = date('Y-m');
    }
    
    $year = (int)substr($period, 0, 4);
    $month = (int)substr($period, 5, 2);
    
    $total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $days_used = get_days_used_in_period($period);
    
    return $total_days - $days_used;
}

/**
 * Get total days in period
 * 
 * @param string $period YYYY-MM
 * @return int
 */
function get_total_days_in_period($period = null) {
    if (!$period) {
        $period = date('Y-m');
    }
    
    $year = (int)substr($period, 0, 4);
    $month = (int)substr($period, 5, 2);
    
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

/**
 * Get period label
 * 
 * @param string $period YYYY-MM
 * @return string e.g., "October 2025"
 */
function get_period_label($period) {
    return date('F Y', strtotime($period . '-01'));
}

// =====================================================================
// ALERT GENERATION
// =====================================================================

/**
 * Get alert thresholds
 * 
 * @return array
 */
function get_alert_thresholds() {
    return [50, 75, 90, 100];
}

/**
 * Check which thresholds are crossed
 * 
 * @param float $percentage_used
 * @param array $thresholds
 * @return array Crossed thresholds
 */
function get_crossed_thresholds($percentage_used, $thresholds = null) {
    if (!$thresholds) {
        $thresholds = get_alert_thresholds();
    }
    
    return array_filter($thresholds, function($threshold) use ($percentage_used) {
        return $percentage_used >= $threshold;
    });
}

/**
 * Generate alert message
 * 
 * @param float $percentage_used
 * @param string $entity_name
 * @param int $threshold
 * @return string
 */
function generate_alert_message($percentage_used, $entity_name, $threshold) {
    $messages = [
        50 => "{entity} has reached 50% of allocated budget",
        75 => "{entity} has reached 75% of allocated budget - WARNING",
        90 => "{entity} has reached 90% of allocated budget - ALERT",
        100 => "{entity} has EXCEEDED allocated budget - CRITICAL"
    ];
    
    $message = $messages[$threshold] ?? "Budget threshold alert for {entity}";
    return str_replace('{entity}', $entity_name, $message);
}

/**
 * Get alert severity level
 * 
 * @param int $threshold
 * @return string 'info' | 'warning' | 'error' | 'critical'
 */
function get_alert_severity($threshold) {
    $severities = [
        50 => 'info',
        75 => 'warning',
        90 => 'error',
        100 => 'critical'
    ];
    
    return $severities[$threshold] ?? 'info';
}

// =====================================================================
// ALLOCATION CALCULATIONS
// =====================================================================

/**
 * Calculate equal allocation
 * 
 * @param float $total
 * @param int $count
 * @return float Amount per entity
 */
function calculate_equal_allocation($total, $count) {
    if ($count <= 0) return 0;
    return $total / $count;
}

/**
 * Calculate proportional allocation
 * 
 * @param array $entities Array of ['id' => ..., 'weight' => ...]
 * @param float $total
 * @return array Allocations per entity
 */
function calculate_proportional_allocation($entities, $total) {
    $result = [];
    
    $total_weight = array_sum(array_column($entities, 'weight'));
    if ($total_weight <= 0) {
        // Fallback to equal
        return array_map(function($e) use ($total, $entities) {
            return ['id' => $e['id'], 'amount' => $total / count($entities)];
        }, $entities);
    }
    
    foreach ($entities as $entity) {
        $proportion = $entity['weight'] / $total_weight;
        $result[] = [
            'id' => $entity['id'],
            'amount' => $total * $proportion
        ];
    }
    
    return $result;
}

// =====================================================================
// HIERARCHY HELPERS
// =====================================================================

/**
 * Get hierarchy label
 * 
 * @param string $hierarchy
 * @return string
 */
function get_hierarchy_label($hierarchy) {
    $labels = [
        'company' => 'Company',
        'pharmacy' => 'Pharmacy',
        'branch' => 'Branch'
    ];
    
    return $labels[strtolower($hierarchy)] ?? $hierarchy;
}

/**
 * Get hierarchy hierarchy (what can allocate to what)
 * 
 * @return array
 */
function get_hierarchy_structure() {
    return [
        'company' => ['pharmacy'],
        'pharmacy' => ['branch'],
        'branch' => []
    ];
}

/**
 * Can allocate from source to target
 * 
 * @param string $source_hierarchy
 * @param string $target_hierarchy
 * @return bool
 */
function can_allocate_to($source_hierarchy, $target_hierarchy) {
    $structure = get_hierarchy_structure();
    $allowed = $structure[strtolower($source_hierarchy)] ?? [];
    
    return in_array(strtolower($target_hierarchy), $allowed);
}

// =====================================================================
// EXPORT HELPERS
// =====================================================================

/**
 * Format data for CSV export
 * 
 * @param array $data
 * @param array $headers
 * @return string CSV content
 */
function format_csv_export($data, $headers) {
    $csv = implode(',', $headers) . "\n";
    
    foreach ($data as $row) {
        $csv_row = [];
        foreach ($headers as $header) {
            $key = strtolower(str_replace(' ', '_', $header));
            $value = $row[$key] ?? '';
            
            // Escape quotes and wrap in quotes
            $value = str_replace('"', '""', $value);
            $csv_row[] = '"' . $value . '"';
        }
        $csv .= implode(',', $csv_row) . "\n";
    }
    
    return $csv;
}

/**
 * Format data for PDF export
 * 
 * @param array $data
 * @param string $title
 * @return array Formatted for PDF library
 */
function format_pdf_export($data, $title) {
    return [
        'title' => $title,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
}
