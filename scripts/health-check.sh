#!/bin/bash

# DocsFlow Health Check Script
# Monitors system health and performance

# Configuration
APP_DIR="/var/www/docflow"
LOG_FILE="/var/log/docflow/health.log"
ALERT_EMAIL="admin@yourdomain.com"
MAX_RESPONSE_TIME=2000  # milliseconds
MIN_DISK_SPACE=10       # GB
MAX_MEMORY_USAGE=80     # percentage
MAX_CPU_USAGE=90        # percentage

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Counters
TOTAL_CHECKS=0
FAILED_CHECKS=0
WARNINGS=0

# Function to log messages
log() {
    local level=$1
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    case $level in
        "ERROR")
            echo -e "${RED}[$timestamp] ERROR: $message${NC}"
            echo "[$timestamp] ERROR: $message" >> $LOG_FILE
            ((FAILED_CHECKS++))
            ;;
        "WARNING") 
            echo -e "${YELLOW}[$timestamp] WARNING: $message${NC}"
            echo "[$timestamp] WARNING: $message" >> $LOG_FILE
            ((WARNINGS++))
            ;;
        "INFO")
            echo -e "${GREEN}[$timestamp] INFO: $message${NC}"
            echo "[$timestamp] INFO: $message" >> $LOG_FILE
            ;;
    esac
    ((TOTAL_CHECKS++))
}

# Function to check HTTP response
check_http_response() {
    local url=$1
    local expected_status=${2:-200}
    local timeout=${3:-10}
    
    local response=$(curl -s -o /dev/null -w "%{http_code}:%{time_total}" --max-time $timeout $url)
    local status_code=$(echo $response | cut -d: -f1)
    local response_time=$(echo $response | cut -d: -f2)
    local response_time_ms=$(echo "$response_time * 1000" | bc)
    
    if [ "$status_code" = "$expected_status" ]; then
        if (( $(echo "$response_time_ms > $MAX_RESPONSE_TIME" | bc -l) )); then
            log "WARNING" "Slow response from $url: ${response_time_ms}ms (expected < ${MAX_RESPONSE_TIME}ms)"
        else
            log "INFO" "$url responding normally: ${status_code} in ${response_time_ms}ms"
        fi
    else
        log "ERROR" "$url returned status $status_code (expected $expected_status)"
    fi
}

# Function to check database connectivity
check_database() {
    cd $APP_DIR
    
    local db_check=$(php artisan tinker --execute="
        try {
            \DB::connection()->getPdo();
            echo 'OK';
        } catch (Exception \$e) {
            echo 'FAILED: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ $db_check == "OK" ]]; then
        log "INFO" "Database connection successful"
        
        # Check database performance
        local query_time=$(php artisan tinker --execute="
            \$start = microtime(true);
            \DB::table('users')->count();
            \$end = microtime(true);
            echo round((\$end - \$start) * 1000, 2);
        " 2>/dev/null)
        
        if (( $(echo "$query_time > 500" | bc -l) )); then
            log "WARNING" "Slow database query: ${query_time}ms"
        else
            log "INFO" "Database query performance: ${query_time}ms"
        fi
    else
        log "ERROR" "Database connection failed: $db_check"
    fi
}

# Function to check queue status
check_queues() {
    cd $APP_DIR
    
    # Check if queue workers are running
    local worker_count=$(pgrep -f "queue:work" | wc -l)
    
    if [ $worker_count -eq 0 ]; then
        log "ERROR" "No queue workers running"
    elif [ $worker_count -lt 2 ]; then
        log "WARNING" "Only $worker_count queue worker running"
    else
        log "INFO" "$worker_count queue workers running"
    fi
    
    # Check failed jobs
    local failed_jobs=$(php artisan queue:failed --format=json | jq length 2>/dev/null || echo "0")
    
    if [ "$failed_jobs" -gt 0 ]; then
        log "WARNING" "$failed_jobs failed jobs in queue"
    else
        log "INFO" "No failed jobs in queue"
    fi
}

# Function to check cache status
check_cache() {
    cd $APP_DIR
    
    local cache_stats=$(php artisan cache:manage stats --format=json 2>/dev/null)
    
    if [ $? -eq 0 ]; then
        local hit_rate=$(echo $cache_stats | jq -r '.hit_rate' 2>/dev/null || echo "0")
        local total_size=$(echo $cache_stats | jq -r '.total_size_mb' 2>/dev/null || echo "0")
        
        if (( $(echo "$hit_rate < 80" | bc -l) )); then
            log "WARNING" "Low cache hit rate: ${hit_rate}%"
        else
            log "INFO" "Cache hit rate: ${hit_rate}%, Size: ${total_size}MB"
        fi
    else
        log "ERROR" "Unable to retrieve cache statistics"
    fi
}

# Function to check disk space
check_disk_space() {
    local available_gb=$(df -BG $APP_DIR | awk 'NR==2 {print $4}' | sed 's/G//')
    
    if [ $available_gb -lt $MIN_DISK_SPACE ]; then
        log "ERROR" "Low disk space: ${available_gb}GB available (minimum: ${MIN_DISK_SPACE}GB)"
    elif [ $available_gb -lt $((MIN_DISK_SPACE * 2)) ]; then
        log "WARNING" "Disk space running low: ${available_gb}GB available"
    else
        log "INFO" "Disk space OK: ${available_gb}GB available"
    fi
}

# Function to check memory usage
check_memory() {
    local memory_usage=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
    
    if (( $(echo "$memory_usage > $MAX_MEMORY_USAGE" | bc -l) )); then
        log "ERROR" "High memory usage: ${memory_usage}%"
    elif (( $(echo "$memory_usage > 70" | bc -l) )); then
        log "WARNING" "Memory usage: ${memory_usage}%"
    else
        log "INFO" "Memory usage: ${memory_usage}%"
    fi
}

# Function to check CPU usage
check_cpu() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
    
    if (( $(echo "$cpu_usage > $MAX_CPU_USAGE" | bc -l) )); then
        log "ERROR" "High CPU usage: ${cpu_usage}%"
    elif (( $(echo "$cpu_usage > 70" | bc -l) )); then
        log "WARNING" "CPU usage: ${cpu_usage}%"
    else
        log "INFO" "CPU usage: ${cpu_usage}%"
    fi
}

# Function to check Laravel services
check_laravel_services() {
    cd $APP_DIR
    
    # Check if application is in maintenance mode
    if [ -f "storage/framework/down" ]; then
        log "WARNING" "Application is in maintenance mode"
    else
        log "INFO" "Application is online"
    fi
    
    # Check storage permissions
    if [ -w "storage/logs" ] && [ -w "storage/app" ] && [ -w "storage/framework" ]; then
        log "INFO" "Storage directories are writable"
    else
        log "ERROR" "Storage directories have permission issues"
    fi
    
    # Check if .env file exists and is readable
    if [ -r ".env" ]; then
        log "INFO" "Environment configuration accessible"
    else
        log "ERROR" "Environment configuration file missing or unreadable"
    fi
}

# Function to check SSL certificate
check_ssl() {
    local domain=$(grep APP_URL .env | cut -d'=' -f2 | sed 's|https://||' | sed 's|http://||')
    
    if [[ $domain == *"https"* ]]; then
        local cert_expiry=$(echo | openssl s_client -connect $domain:443 -servername $domain 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d'=' -f2)
        local expiry_epoch=$(date -d "$cert_expiry" +%s)
        local current_epoch=$(date +%s)
        local days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))
        
        if [ $days_until_expiry -lt 7 ]; then
            log "ERROR" "SSL certificate expires in $days_until_expiry days"
        elif [ $days_until_expiry -lt 30 ]; then
            log "WARNING" "SSL certificate expires in $days_until_expiry days"
        else
            log "INFO" "SSL certificate valid for $days_until_expiry days"
        fi
    fi
}

# Function to send alert email
send_alert() {
    if [ $FAILED_CHECKS -gt 0 ] || [ $WARNINGS -gt 3 ]; then
        local subject="DocsFlow Health Check Alert - $FAILED_CHECKS errors, $WARNINGS warnings"
        local body="DocsFlow health check completed at $(date)\n\nSummary:\n- Total checks: $TOTAL_CHECKS\n- Failed checks: $FAILED_CHECKS\n- Warnings: $WARNINGS\n\nSee $LOG_FILE for details."
        
        echo -e "$body" | mail -s "$subject" $ALERT_EMAIL 2>/dev/null || log "WARNING" "Failed to send alert email"
    fi
}

# Main execution
echo "Starting DocsFlow health check at $(date)"

# Create log directory if it doesn't exist
mkdir -p $(dirname $LOG_FILE)

# Run all checks
check_http_response "http://localhost" 200
check_http_response "http://localhost/login" 200
check_database
check_queues
check_cache
check_disk_space
check_memory
check_cpu
check_laravel_services
check_ssl

# Generate summary
echo ""
echo "=== HEALTH CHECK SUMMARY ==="
echo "Total checks: $TOTAL_CHECKS"
echo "Failed checks: $FAILED_CHECKS"
echo "Warnings: $WARNINGS"

if [ $FAILED_CHECKS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}System status: HEALTHY${NC}"
    exit 0
elif [ $FAILED_CHECKS -eq 0 ]; then
    echo -e "${YELLOW}System status: WARNINGS${NC}"
    exit 0
else
    echo -e "${RED}System status: CRITICAL${NC}"
    send_alert
    exit 1
fi