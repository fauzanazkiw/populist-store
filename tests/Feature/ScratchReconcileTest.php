<?php
it('calls number_format', function () {
    expect(number_format(100000, 0, ',', '.'))->toBe('100.000');
});
it('does string ops without number_format', function () {
    expect(strtoupper('abc'))->toBe('ABC');
});
