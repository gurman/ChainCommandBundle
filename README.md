# ChainCommandBundle
Symfony bundle providing simple command chaining. You can make chains from commands. Each chain consists of one main command
and set of member commands. Execution of main command will execute also all it's member commands. Also you cannot execute
member command without whole chain.

Installation
------------

```bash
 composer require "gurman/chain-command-bundle": "*"
```

Usage
======
```yaml
chain_command:
  chains:
    -
      main_command: 'foo:hello'
      members:
        - { command: 'bar:hi', arguments: '' }
```
