#!/bin/bash

DEPLOY_PATH=$1

if [ -z "$DEPLOY_PATH" ]; then
  echo "用法: $0 <部署路径>"
  exit 1
fi

git fetch origin
# 判断有没有更新
if [ $(git rev-parse HEAD) != $(git rev-parse @{u}) ]
then
  echo "有新的提交，执行build"
  git pull origin
 else
  echo "没有新的提交"
  exit 0
fi

# 安装依赖并打包
pnpm install
pnpm run build

#部署到线上
rm -rf "$DEPLOY_PATH"/*
cp -r ./dist/index.html "$DEPLOY_PATH"/
cp -r ./dist/admin/* "$DEPLOY_PATH"/
