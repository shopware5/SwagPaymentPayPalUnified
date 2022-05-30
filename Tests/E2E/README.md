### How to Setup the E2E Tests
1. Install NPM
2. Install E2E Packages
```
cd Tests/E2E
npm install
```
3. Install playwright browser and dependencies
   1. debian based system
    ```
    npx playwright install --with-deps
    ```
   2. other
      1. Install browser and driver: chromium chromium-chromedriver
      2. Adjust EXECUTION_PATH in the .env file
   3. docker based
      1. Spawn a playwright container e.g., with docker-compose:
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

6. Run single test file
```
npm run e2e:run express
```

Now only every test file that matches the name "express" is executed.

7. Debug single test
```
npm run e2e:run:debug:test "Check product listing page @notIn5.2"
```

# Debug Trace:

1. go to https://trace.playwright.dev/

2. Unzip the artifacts and load the trace of the test

# Create a basic test with CODEGEN.

1. Got to .../Tests/E2E/test
2. Execute ```npx playwright codegen YOUR_URL``` and ensure to replace YOUR_URL with your url like "shopware.localhost/backend"
3. Click through the page to generate a basic test code
4. Copy the test code to your test file
5. Adjust the test with waiting for the loading states, expectations and other.
