status is-interactive || exit

if test (id -u) -eq 0
    set -gx PATH /miz/bin $PATH
end

if type -q zoxide
    zoxide init fish | source
end

if type -q direnv
    direnv hook fish | source
end

function fish_prompt
    set -l last_status $status
    set_color brgreen
    echo -n [(whoami)]
    echo -n ' '

    set_color brred
    echo -n (prompt_pwd)

    if test $last_status -ne 0
        echo -n (set_color brmagenta)" [$last_status]"
    end

    echo
    set_color bryellow
    echo -n "> "

    set_color normal
end

function docker_renew
    docker compose pull
    docker compose down
    docker compose up -d
end

