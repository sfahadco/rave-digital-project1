# Steps
- Create docker compose file with three services viz postgres, redis and ollama
- validate the compose file as
```
docker compose config
```
- Start the containers as
```
docker compose up -d
```
- Run following POST request to check vector length
```
POST http://localhost:11434/api/embed
Content-Type: application/json

{"model": "nomic-embed-text", "input": "test string"}
```

- Measure Ollama Startup Performance Metric

# Commands
### Stop and Remove specific container with volume
```
docker compose rm -s -v <service name> 
```



## 3.2B Ollama Startup Performance Metric (Cold vs Warm)

- Restart the ollama container
````
docker compose restart ollama
````

- Run following two times
````
time curl -s http://localhost:11434/api/generate   -d '{"model":"llama3.2:3b","prompt":"Say hello.","stream":false}'
````


### Output

| Metric | Cold (after restart) | Warm | Delta |
|---|---|---|---|
| `real` (wall clock) | 7.078 s | 0.864 s | −6.21 s |
| `total_duration` | 7.013 s | 0.838 s | −6.17 s |
| `load_duration` | 5.069 s | 0.178 s | **−4.89 s** |
| `prompt_eval_duration` | 0.417 s | 0.060 s | −0.36 s |
| `eval_duration` | 1.524 s | 0.598 s | — |
| `prompt_eval_count` | 28 | 28 | — |
| `eval_count` | 25 | 10 | — |
| Throughput | 16.4 tok/s | 16.7 tok/s | ~flat |
