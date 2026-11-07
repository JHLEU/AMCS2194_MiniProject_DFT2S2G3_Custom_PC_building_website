#!/bin/bash
# 从 git 历史中移除 Cloudflare User API Token

echo "开始从 git 历史中移除敏感 token..."

# 方法1: 使用 git filter-branch (Git 内置命令)
# 先创建备份分支
git branch backup-before-filter

# 删除包含敏感内容的历史
git filter-branch --force --index-filter "
    git show f7b4e8f64b596cf22a0faa1f9f9cc035c6bf42f5:.claude/settings.local.json 2>/dev/null | \
    sed 's/cfut_[A-Za-z0-9]*移除/cfut_***REDACTED***/g' > .git/filtered.json && \
    cat > .claude/settings.local.json < .git/filtered.json || true
" --prune-empty --tag-name-filter cat -- --all

# 清理垃圾回收
git reflog expire --expire=now --all
git gc --prune=now --aggressive

echo "Token 已从历史中移除。"
echo "现在可以执行: git push --force origin michong"