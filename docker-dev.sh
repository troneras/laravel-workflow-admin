#!/bin/bash

# Laravel AI Orchestrator - Docker Development Environment
# This script provides convenient commands for managing the dockerized development environment

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE} Laravel AI Orchestrator Docker ${NC}"
    echo -e "${BLUE}================================${NC}"
    echo
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

case "$1" in
    "up"|"start")
        print_header
        echo "Starting development environment..."
        
        # Start services
        docker-compose up -d
        
        # Wait a moment for services to initialize
        sleep 3
        
        # Initialize Laravel application
        echo "Initializing Laravel application..."
        docker-compose exec app sh -c "
            php artisan key:generate --ansi --no-interaction &&
            php artisan migrate --force --no-interaction
        " || {
            print_error "Failed to initialize Laravel application"
            print_warning "Try running: ./docker-dev.sh logs app"
            exit 1
        }
        
        print_success "Development environment started!"
        print_success "Application: http://localhost:8088"
        print_success "Vite dev server: http://localhost:5173"
        print_success "Services: Web (FrankenPHP), Queue, Scheduler, Logs, Node"
        ;;
        
    "down"|"stop")
        print_header
        echo "Stopping development environment..."
        docker-compose down
        print_success "Development environment stopped!"
        ;;
        
    "restart")
        print_header
        echo "Restarting development environment..."
        docker-compose down
        docker-compose up -d
        print_success "Development environment restarted!"
        ;;
        
    "build")
        print_header
        echo "Building Docker images..."
        docker-compose build --no-cache
        print_success "Docker images built!"
        ;;
        
    "logs")
        print_header
        if [ -n "$2" ]; then
            docker-compose logs -f "$2"
        else
            docker-compose logs -f
        fi
        ;;
        
    "shell"|"bash")
        print_header
        docker-compose exec app bash
        ;;
        
    "artisan")
        shift
        docker-compose exec app php artisan "$@"
        ;;
        
    "composer")
        shift
        docker-compose exec app composer "$@"
        ;;
        
    "npm")
        shift
        docker-compose exec node npm "$@"
        ;;
        
    "test")
        print_header
        echo "Running tests..."
        docker-compose exec app php artisan test
        ;;

    "cache"|"clear-cache")
        print_header
        echo "Clearing Laravel caches..."
        docker-compose exec app sh -c "\
            php artisan optimize:clear --no-interaction && \
            php artisan cache:clear --no-interaction && \
            php artisan config:clear --no-interaction && \
            php artisan route:clear --no-interaction && \
            php artisan view:clear --no-interaction\
        " && print_success "Laravel caches cleared!" || print_error "Failed to clear caches"
        ;;
        
    "fresh")
        print_header
        echo "Fresh installation (this will reset your database)..."
        docker-compose exec app php artisan migrate:fresh --seed
        print_success "Database reset and seeded!"
        ;;
        
    "clean")
        print_header
        echo "Cleaning up Docker environment..."
        docker-compose down -v
        docker system prune -f
        docker volume prune -f
        print_success "Docker environment cleaned!"
        ;;
        
    "redis-clear"|"flush-redis")
        print_header
        echo "Flushing Redis data..."
        docker-compose exec redis sh -c "redis-cli FLUSHALL" \
            && print_success "Redis FLUSHALL executed" \
            || print_error "Failed to flush Redis"
        ;;

    "status")
        print_header
        docker-compose ps
        ;;
        
    *)
        print_header
        echo "Usage: $0 {up|down|restart|build|logs|shell|artisan|composer|npm|test|fresh|clean|redis-clear|status}"
        echo
        echo "Commands:"
        echo "  up/start    - Start the development environment"
        echo "  down/stop   - Stop the development environment"
        echo "  restart     - Restart the development environment"
        echo "  build       - Build Docker images from scratch"
        echo "  logs [svc]  - Show logs (optionally for specific service)"
        echo "  shell/bash  - Access application container shell"
        echo "  artisan     - Run Laravel Artisan commands"
        echo "  composer    - Run Composer commands"
        echo "  npm         - Run NPM commands"
        echo "  test        - Run Laravel tests"
        echo "  cache       - Clear Laravel caches (optimize, app, config, route, view)"
        echo "  redis-clear - Flush all Redis data (FLUSHALL)"
        echo "  fresh       - Reset database and run seeders"
        echo "  clean       - Clean up Docker environment"
        echo "  status      - Show container status"
        echo
        echo "Examples:"
        echo "  $0 up                    # Start development environment"
        echo "  $0 artisan migrate       # Run migrations"
        echo "  $0 logs app              # Show application logs"
        echo "  $0 npm run build         # Build frontend assets"
        ;;
esac