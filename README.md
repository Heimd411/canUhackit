# Web Security Challenge Lab

A containerized web application featuring various security challenges.

## Quick Start

1. [WINDOWS] Install Docker Desktop [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)<br>
   [LINUX] Refere to [https://docs.docker.com/engine/install/](https://docs.docker.com/engine/install/)

3. Open terminal/command prompt and run:
```bash
# Pull and run the application
git clone https://github.com/Heimd411/canUhackit.git
cd canUhackit
docker-compose up -d
```

3. Access the application at [http://localhost:5001](http://localhost:5001)

## Challenges Available
- Username Enumeration via Response Time
- Username Enumeration via Subtly Different Responses
- Authentication Oracle
- And more...

## System Requirements
- Docker Desktop
- 4GB RAM minimum
- 10GB free disk space

## Troubleshooting
If you encounter permission issues on Windows, run:
```bash
docker-compose down
docker volume prune -f
docker-compose up -d
```
