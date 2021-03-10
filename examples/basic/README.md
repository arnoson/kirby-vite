# Kirby Vite Basic Example

## Installation

- run `npm install` inside the root directory
- run `composer install` inside the `/public` directory

## Development

- run `npm run server` and `npm run dev` inside the root directory
- visit `localhost:8888` in the browser and make some changes to the js and css files inside `/src` or modify the default template in `/public/templates`

## Production

- run `npm run build`
- now the `/public` folder is ready to be deployed! To test the production version
  locally, uncomment `'dev' => true` in `/public/site/config/config.localhost.php`
