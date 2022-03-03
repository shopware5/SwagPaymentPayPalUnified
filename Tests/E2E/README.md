### How to Setup the E2E Tests

1. Install NPM
2. Install E2E Packages
```
cd Tests/E2E
npm install
```
3. Install playwright browser and dependencies
   3.1 (Debian based) Install playwright browser and dependencies
```
npx playwright install --with-deps
```
3.2.1 (Other) Install browser and driver: chromium chromium-chromedriver
3.2.2 (Other) Adjust EXECUTION_PATH in the .env file
3.3 (Docker Based) Spawn a playwright container e.g., with docker-compose:
```
playwright:
    image: mcr.microsoft.com/playwright:focal
    working_dir: /Tests
    user: "1000:100"
    environment:
        - DISPLAY
    volumes:
./shopware/custom/plugins/SwagPaymentPayPalUnified/Tests/E2E:/Tests
file
        - /tmp/.X11-unix:/tmp/.X11-unix
```
And then
```
docker-compose run playwright bash
npm install
```
4. Create your own .env file
```
cp .env.dist .env
```
5. Run tests
```
npm run e2e:run
```
