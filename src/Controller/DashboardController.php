<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProduitRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_dashboard')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ARTISAN')")]
    public function index(UserRepository $userRepository, OrdersRepository $ordersRepository, ProduitRepository $produitRepository): Response
    {
        // Get counts for the dashboard
        $totalUsers = $userRepository->count([]);
        $totalOrders = $ordersRepository->count([]);
        $totalProducts = $produitRepository->count([]);
        
        // Get recent orders
        $recentOrders = $ordersRepository->findBy([], ['createdAt' => 'DESC'], 5);
        
        // Get data for charts
        $monthlySales = $this->getMonthlySalesData($ordersRepository);
        $userRegistrations = $this->getUserRegistrationData($userRepository);
        
        return $this->render('dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'recentOrders' => $recentOrders,
            'monthlySales' => $monthlySales,
            'userRegistrations' => $userRegistrations,
        ]);
    }
    
    private function getMonthlySalesData(OrdersRepository $ordersRepository): array
    {
        // Get sales data for the last 6 months
        $data = [];
        $months = [];
        $sales = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $months[] = $date->format('M Y');
            $startDate = new \DateTime($date->format('Y-m-01 00:00:00'));
            $endDate = new \DateTime($date->format('Y-m-t 23:59:59'));
            
            $monthlyOrders = $ordersRepository->createQueryBuilder('o')
                ->select('COUNT(o.order_id) as orderCount', 'SUM(o.total_price) as total')
                ->where('o.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleResult();
                
            $sales[] = (float)($monthlyOrders['total'] ?? 0);
        }
        
        return [
            'labels' => $months,
            'data' => $sales
        ];
    }
    
    private function getUserRegistrationData(UserRepository $userRepository): array
    {
        // Get user registration data for the last 6 months
        $data = [];
        $months = [];
        $counts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $months[] = $date->format('M Y');
            $startDate = new \DateTime($date->format('Y-m-01 00:00:00'));
            $endDate = new \DateTime($date->format('Y-m-t 23:59:59'));
            
            $count = $userRepository->createQueryBuilder('u')
                ->select('COUNT(u.idUser)')
                ->where('u.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleScalarResult();
                
            $counts[] = (int)$count;
        }
        
        return [
            'labels' => $months,
            'data' => $counts
        ];
    }
}
