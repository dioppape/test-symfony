<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MedecinController extends Controller
{
    /**
     * @Route("/medecins", name="medecins")
     *
     *
     * @return Response
     */
    public function medecinsAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $medecins = $manager->getRepository('AppBundle:Medecin')->findAll();

        return $this->render('medecin/medecins.html.twig', ['medecins' => $medecins]);
    }

    /**
     * @Route("/medecins/patient/{identifiant}", name="medecins_du_patient")
     * @param $identifiant
     *
     * @return Response
     */
    public function medecinDuPatientAction($identifiant)
    {
        $manager = $this->getDoctrine()->getManager();
        $medecins = $manager->getRepository('AppBundle:Medecin')->findMedecinDuPatient($identifiant);


        $patient = $manager->getRepository('AppBundle:Patient')->findOneByIdentifiant($identifiant);

        return $this->render('medecin/medecins-du-patient.html.twig', ['patient' => $patient, 'medecins' => $medecins]);
    }
}
