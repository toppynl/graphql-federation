<?php
namespace Toppynl\GraphQLFederation\Printer;

use GraphQL\Utils\SchemaPrinter;
use Toppynl\GraphQLFederation\FederatedSchema;

class FederatedSchemaPrinter
{
    private const FEDERATION_URL = 'https://specs.apollo.dev/federation/v2.6';

    private const EXCLUDED_TYPES = [
        '_Any', '_Entity', '_Service', 'FieldSet',
        'String', 'Boolean', 'Int', 'Float', 'ID',
    ];

    private const EXCLUDED_QUERY_FIELDS = ['_entities', '_service'];

    public static function printForService(FederatedSchema $fedSchema): string
    {
        $parts = [];

        // @link schema extension block
        $imports = ['@key', '@external', '@requires', '@provides', '@shareable', '@inaccessible', '@override'];
        $importList = implode(', ', array_map(fn ($i) => '"' . $i . '"', $imports));
        $parts[] = sprintf('extend schema @link(url: "%s", import: [%s])', self::FEDERATION_URL, $importList);

        // Get raw SDL from webonyx printer
        $sdl = SchemaPrinter::doPrint($fedSchema->schema);

        // Inject @key annotations on entity types
        foreach ($fedSchema->entityConfigs as $config) {
            $annotation = sprintf('@key(fields: "%s")', $config->fields);
            $sdl = preg_replace(
                '/^(type\s+' . preg_quote($config->typeName, '/') . '\b[^{]*)\{/m',
                '$1' . $annotation . ' {',
                $sdl,
            );
        }

        // Filter out excluded types and federation query fields
        $parts[] = self::filterSdl($sdl);

        return implode("\n\n", array_filter($parts)) . "\n";
    }

    private static function filterSdl(string $sdl): string
    {
        $lines  = explode("\n", $sdl);
        $output = [];
        $skip   = false;
        $depth  = 0;

        foreach ($lines as $line) {
            // Detect start of a type block to potentially skip
            if (!$skip && preg_match('/^(?:type|scalar|union|interface|enum|input)\s+(\w+)/', $line, $m)) {
                if (in_array($m[1], self::EXCLUDED_TYPES, true)) {
                    $skip  = true;
                    $depth = 0;
                }
            }

            if ($skip) {
                $opens  = substr_count($line, '{');
                $closes = substr_count($line, '}');
                $depth += $opens - $closes;
                // End skip when: a braced block closes (depth back to 0 with a closing brace),
                // OR the excluded declaration was brace-less (scalar / union inline) so depth
                // never went above 0 and the current line itself had no opening brace.
                if ($depth <= 0 && ($closes > 0 || preg_match('/^scalar\s/', $line) || $opens === 0)) {
                    $skip = false;
                }
                continue;
            }

            // Drop lines that are federation-injected query fields
            $skipLine = false;
            foreach (self::EXCLUDED_QUERY_FIELDS as $field) {
                if (preg_match('/^\s+' . preg_quote($field, '/') . '[\s:(]/', $line)) {
                    $skipLine = true;
                    break;
                }
            }

            if (!$skipLine) {
                $output[] = $line;
            }
        }

        // Collapse multiple blank lines into one
        $result = preg_replace('/\n{3,}/', "\n\n", implode("\n", $output));
        return trim($result);
    }
}
