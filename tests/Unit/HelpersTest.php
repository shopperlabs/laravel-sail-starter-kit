<?php

declare(strict_types=1);

use function App\updateEnvVariable;

beforeEach(function (): void {
    $this->tempEnvPath = base_path('.env.testing.temp');
    $this->originalEnvPath = base_path('.env');

    // Backup original .env
    if (file_exists($this->originalEnvPath)) {
        copy($this->originalEnvPath, $this->originalEnvPath.'.backup');
    }

    // Create a temporary .env file for testing
    file_put_contents($this->tempEnvPath, "EXISTING_KEY=old_value\nANOTHER_KEY=123\n");

    // Replace .env with temp file
    copy($this->tempEnvPath, $this->originalEnvPath);
});

afterEach(function (): void {
    // Restore original .env
    if (file_exists($this->originalEnvPath.'.backup')) {
        copy($this->originalEnvPath.'.backup', $this->originalEnvPath);
        unlink($this->originalEnvPath.'.backup');
    }

    // Clean up temp file
    if (file_exists($this->tempEnvPath)) {
        unlink($this->tempEnvPath);
    }
});

describe('updateEnvVariable', function (): void {
    test('can update existing variable with string value', function (): void {
        updateEnvVariable('EXISTING_KEY', 'new_value');

        $content = file_get_contents(base_path('.env'));

        expect($content)
            ->toContain('EXISTING_KEY="new_value"')
            ->not->toContain('EXISTING_KEY=old_value');
    });

    test('can update existing variable with numeric value', function (): void {
        updateEnvVariable('ANOTHER_KEY', 456);

        $content = file_get_contents(base_path('.env'));

        expect($content)
            ->toContain('ANOTHER_KEY=456')
            ->not->toContain('ANOTHER_KEY=123');
    });

    test('can update existing variable with boolean true', function (): void {
        updateEnvVariable('EXISTING_KEY', true);

        $content = file_get_contents(base_path('.env'));

        expect($content)
            ->toContain('EXISTING_KEY=true')
            ->not->toContain('EXISTING_KEY=old_value');
    });

    test('can update existing variable with boolean false', function (): void {
        updateEnvVariable('EXISTING_KEY', false);

        $content = file_get_contents(base_path('.env'));

        expect($content)
            ->toContain('EXISTING_KEY=false')
            ->not->toContain('EXISTING_KEY=old_value');
    });

    test('can add new variable that does not exist', function (): void {
        updateEnvVariable('NEW_KEY', 'new_value');

        $content = file_get_contents(base_path('.env'));

        expect($content)->toContain('NEW_KEY="new_value"');
    });

    test('can add new variable with numeric value', function (): void {
        updateEnvVariable('NEW_NUMERIC_KEY', 999);

        $content = file_get_contents(base_path('.env'));

        expect($content)->toContain('NEW_NUMERIC_KEY=999');
    });

    test('can add new variable with boolean value', function (): void {
        updateEnvVariable('NEW_BOOL_KEY', true);

        $content = file_get_contents(base_path('.env'));

        expect($content)->toContain('NEW_BOOL_KEY=true');
    });

    test('handles numeric string values correctly', function (): void {
        updateEnvVariable('NUMERIC_STRING_KEY', '12345');

        $content = file_get_contents(base_path('.env'));

        expect($content)->toContain('NUMERIC_STRING_KEY=12345');
    });

    test('handles string values with spaces correctly', function (): void {
        updateEnvVariable('STRING_WITH_SPACES', 'value with spaces');

        $content = file_get_contents(base_path('.env'));

        expect($content)->toContain('STRING_WITH_SPACES="value with spaces"');
    });

    test('preserves other variables when updating', function (): void {
        updateEnvVariable('EXISTING_KEY', 'updated');

        $content = file_get_contents(base_path('.env'));

        expect($content)
            ->toContain('ANOTHER_KEY=123')
            ->toContain('EXISTING_KEY="updated"');
    });
});
