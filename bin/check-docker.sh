#!/usr/bin/env bash

echo "curl -X GET \"http://elasticsearch:9200/\""
curl -X GET "http://elasticsearch:9200/" -u elastic:password
curl -X GET "http://elasticsearch:9200/"

echo "curl -X GET \"http://elasticsearch:7103/\""
curl -X GET "http://elasticsearch:7103/" -u elastic:password
curl -X GET "http://elasticsearch:7103/"

echo "curl -X GET \"http://localhost:9200/\""
curl -X GET "http://localhost:9200/" -u elastic:password
curl -X GET "http://localhost:9200/"

echo "curl -X GET \"http://localhost:7103/\""
curl -X GET "http://localhost:7103/" -u elastic:password
curl -X GET "http://localhost:7103/"
