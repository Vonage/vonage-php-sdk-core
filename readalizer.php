<?php

declare(strict_types=1);

use Readalizer\Readalizer\Rules\RequireNamespaceRule;
use Readalizer\Readalizer\Rules\StrictTypesDeclarationRule;

/**
 * Copy this to readalizer.php in your project root and configure your rules.
 *
 * Each rule is a class implementing RuleInterface (node-level) or
 * FileRuleInterface (file-level). Rules can live anywhere.
 *
 * ── Suppressing violations ───────────────────────────────────────────────────
 *
 * PHP attribute on a class, method, property, or parameter:
 *
 *   use Readalizer\Readalizer\Attributes\Suppress;
 *
 *   #[Suppress]                                   // suppress ALL rules
 *   #[Suppress(NoLongMethodsRule::class)]          // suppress one rule
 *   #[Suppress(RuleA::class, RuleB::class)]        // suppress multiple
 *
 * Scope: a class-level attribute suppresses everything within the class;
 * a method-level attribute suppresses everything within that method.
 *
 * Inline comment for line-level suppression (trailing or preceding line):
 *
 *   $x = something(); // @readalizer-suppress NoLongMethodsRule
 *   // @readalizer-suppress                   (preceding line, suppress all)
 *   // @readalizer-suppress RuleA, RuleB      (preceding line, suppress named)
 */
return [

    // Paths to scan when no paths are passed on the CLI.
    'paths' => [
        'src',
    ],

    // Memory limit for analysis (default: 2G).
    'memory_limit' => '2G',

    // Cache results between runs.
    'cache' => [
        'enabled' => true,
        'path' => '.readalizer-cache.json',
    ],

    // Optional baseline file to suppress known violations.
    // 'baseline' => '.readalizer-baseline.json',

    // Paths, directory prefixes, or glob patterns to exclude from scanning.
    'ignore' => [
        // 'rector.php',
        // 'src/',
        // '*.generated.php',
    ],

    // Choose one or more rulesets (packs).
    'ruleset' => [
        // new FileStructureRuleset(),
        // new TypeSafetyRuleset(),
        // new ClassDesignRuleset(),
        // new MethodDesignRuleset(),
        // new NamingRuleset(),
        // new ExpressionRuleset(),
    ],

    // Add or override rules on top of rulesets.
    'rules' => [
        // File structure
        new RequireNamespaceRule(),
        new StrictTypesDeclarationRule()

        // new LineLengthRule(maxLeng)th: 120),
        // new CustomFileRule(),

        // Type safety
        // new CustomTypeRule(),

        // Class design
        // new CustomClassRule(),

        // Method design
        // new CustomMethodRule(),

        // Naming conventions
        // new CustomNamingRule(),

        // Expressions & control flow
        // new CustomExpressionRule(),
    ],

];
