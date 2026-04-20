# Queue Workers (Redis)

This app is configured to use Redis for cache, sessions, and queues. See `.env` and `.env.example` for the Redis settings.

**Recommended `.env` settings**
- `CACHE_DRIVER=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `REDIS_DB=0`
- `REDIS_CACHE_DB=1`
- `REDIS_QUEUE_DB=2`
- `REDIS_QUEUE_RETRY_AFTER=90`

**Local dev run (one worker)**
```bash
php artisan queue:work redis --sleep=1 --tries=3 --timeout=90 --max-time=3600
```

**Supervisor config (Linux)**
- Use `deploy/supervisor/laravel-queue-worker.conf`.
- Replace `directory` with your project path.
- Adjust `numprocs` to match CPU cores and workload.

**Deploy note**
- After deployment, run `php artisan queue:restart` to reload workers.
