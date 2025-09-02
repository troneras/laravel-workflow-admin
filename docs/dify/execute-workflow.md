We want in this application to test how to integrate a laravel app with dify. Dify allows to run AI workflows.     │
│   The way dify works is that you POST to an api endpoint with the input information using a workflow unique API      │
│   key. The workflows will run asyncronously and eventually will end with some output. I want a very simple           │
│   application where the user can create tasks, and will be displayed in a table. When the user clicks on run, a      │
│   task to run the workflow will be created, the workflow can be run multiple times, so we want to be able to see a   │
│   task execution list clicking on a button from the task table. The task execution item will store                   │
│   task_execution_id,  task_id, start/end time, duration, tokens, status, input and output, track (logs)