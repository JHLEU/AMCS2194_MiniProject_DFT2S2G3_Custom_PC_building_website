# 移除 Git 历史中的敏感 Token

## 问题描述
在 commit `f7b4e8f` 中，Cloudflare User API Token 被提交到了 git 历史中：
- Token: `cfut_AEH9nHLKJas0OZLxgBwDKk93jMIe2pPhdZdY9ljc81d3bd0f`
- 位置: `.claude/settings.local.json`

GitHub push 保护阻止了推送，需要从整个 git 历史中移除这个 token。

## 解决方案

### 方法 1: 使用 git filter-repo（推荐）

```bash
# 1. 安装 filter-repo（如果尚未安装）
pip install git-filter-repo

# 2. 备份当前分支
git branch backup

# 3. 移除敏感文件
git filter-repo --path .claude/settings.local.json --invert-paths

# 4. 强制推送
git push --force origin michong
```

### 方法 2: 使用 git filter-branch

```bash
# 1. 备份当前分支
git branch backup

# 2. 强制移除并替换为红标内容
git filter-branch --force --index-filter \
    "git show f7b4e8f:.claude/settings.local.json 2>/dev/null | sed 's/cfut_[A-Za-z0-9]*/cfut_***REDACTED***/g' > .git/tokens.txt && \
    cat .git/tokens.txt" \
    --prune-empty --tag-name-filter cat -- --all

# 3. 清理
git reflog expire --expire=now --all
git gc --prune=now

# 4. 强制推送
git push --force origin michong
```

### 方法 3: 手动创建新提交（较慢，但更可控）

```bash
# 1. 备份当前分支
git branch backup

# 2. 查看包含 token 的提交
git log --oneline -5
git show f7b4e8f:.claude/settings.local.json

# 3. 在本地修改文件，移除 token
cat > .claude/settings.local.json << 'EOF'
{
  "$schema": "https://json.schemastore.org/claude-code-settings.json",
  "env": {
    "ANTHROPIC_BASE_URL": "http://localhost:20128/v1",
    "ANTHROPIC_AUTH_TOKEN": "cfut_***REDACTED***",
    "ANTHROPIC_MODEL": "claude-free"
  },
  "model": "sonnet"
}
EOF

# 4. 重置到包含 token 之前的那个提交
git reset --hard f7b4e8f~

# 5. 重新添加修改后的文件
git add .claude/settings.local.json
git commit -m "Replace settings.local.json without sensitive token"

# 6. 强制推送
git push --force origin michong
```

## 选择哪个方法？

- **方法 1 (git filter-repo)**: 最快、最现代，推荐使用
- **方法 2 (git filter-branch)**: 传统方法，较慢但稳定
- **方法 3 (手动重置)**: 最可控，但需要多次 commit 操作

## 验证步骤

```bash
# 推送后验证 token 已不在历史中
grep -r "cfut_" .git  # 不应该有结果
git log --all --source --full-history --oneline -- .claude/settings.local.json
```

## 注意事项

1. ⚠️ **强制推送会改写历史** - 所有协作者需要重新 clone
2. ⚠️ 最好先备份当前分支
3. ⚠️ 推送后，token 在历史中已被移除，但其他团队成员需要重新拉取