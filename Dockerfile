
# Use an official Node.js runtime as a parent image
FROM node:20-alpine

# Create app directory
WORKDIR /app

# Install app dependencies
# A wildcard is used to ensure both package.json AND package-lock.json are copied
COPY package*.json ./

RUN npm install

# Copy app source code
COPY . .

# Build the Nuxt application
RUN npm run build

# Expose the port the app runs on (Nuxt default is 3000, but often served by Nginx or similar in production)
EXPOSE 3000

# Start the Nuxt application
CMD [ "npm", "run", "dev" ]

