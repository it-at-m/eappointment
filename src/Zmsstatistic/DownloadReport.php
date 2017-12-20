<?php
/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class DownloadReport extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $scope = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] .'/')->getEntity();
        $department = \App::$http->readGetResult('/scope/' . $scope->id .'/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $department->id .'/organisation/')->getEntity();

        $download = (new Helper\Download($request, $args))->setReportWriter($scope, $department, $organisation);

        $response->getBody()->write($download->getWriter()->save('php://output'));
        return $response
            ->withHeader(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            )
            ->withHeader(
                'Content-Disposition',
                sprintf('attachment; filename="%s.%s"', $download->getTitle(), $download->getType())
            );
    }
}
