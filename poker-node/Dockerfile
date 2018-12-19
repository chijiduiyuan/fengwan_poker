FROM mhart/alpine-node:6.11

RUN mkdir /src

RUN npm install -g cnpm --registry=https://registry.npm.taobao.org

WORKDIR /src
ADD package.json package.json
RUN cnpm install && cnpm install pomelo -g
