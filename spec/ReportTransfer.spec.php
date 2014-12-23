<?php

/**
 * This file is part of CoverallsKit.
 *
 * (c) Noritaka Horio <holy.shared.design@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace coverallskit\spec;

use coverallskit\ReportTransfer;
use coverallskit\entity\Report;
use coverallskit\entity\Repository;
use coverallskit\entity\collection\SourceFileCollection;
use coverallskit\entity\service\travis\TravisCI;
use Prophecy\Prophet;
use Prophecy\Argument;


describe('ReportTransfer', function() {
    describe('getClient', function() {
        beforeEach(function() {
            $this->uploader = new ReportTransfer();
        });
        context('when not specified client', function() {
            it('should return Guzzle\Http\Client instance', function() {
                $client = $this->uploader->getClient();
                expect($client)->toBeAnInstanceOf('GuzzleHttp\Client');
            });
        });
    });

    describe('upload', function() {
        beforeEach(function() {
            $this->prophet = new Prophet();

            $this->service = new TravisCI([
                'service_job_id' => '10',
                'service_name' => 'travis-ci'
            ]);

            $this->report = new Report([
                'name' => __DIR__ . '/fixtures/coveralls.json',
                'token' => 'foo',
                'repository' => new Repository(__DIR__ . '/../'),
                'service' => $this->service,
                'sourceFiles' => new SourceFileCollection()
            ]);

            $url = ReportTransfer::ENDPOINT_URL;
            $optionsCallback = Argument::that(function(array $options) {
                return isset($options['body']);
            });

            $this->client = $this->prophet->prophesize('GuzzleHttp\ClientInterface');
            $this->client->post($url, $optionsCallback)->shouldBeCalled();

            $this->uploader = new ReportTransfer($this->client->reveal());
            $this->uploader->upload($this->report);
        });
        it('should upload report file', function() {
            $this->prophet->checkPredictions();
        });
    });
});