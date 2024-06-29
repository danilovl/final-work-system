#!/usr/bin/env bash

curl -X DELETE "http://elasticsearch:9200/_security/user/superuser" -u elastic:password

curl -X PUT "http://elasticsearch:9200/_security/user/superuser" -H "Content-Type: application/json" -d '{
  "password": "superuser",
  "roles": ["superuser", "kibana_admin", "kibana_system"]
}' -u elastic:password
