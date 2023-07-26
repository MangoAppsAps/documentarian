<?php

namespace Mpociot\Documentarian\Overrides;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverterInterface;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;

class GitHubFlavouredCommonMarkParser extends CommonMarkParser
{
    public function __construct(MarkdownConverterInterface $commonMarkConverter = null)
    {
        parent::__construct($commonMarkConverter ?: new GithubFlavoredMarkdownConverter);
    }
}
