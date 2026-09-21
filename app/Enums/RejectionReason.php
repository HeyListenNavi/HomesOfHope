<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RejectionReason: string implements HasColor, HasLabel
{
    case NoChildren = 'no_children';

    case ContractIssues = 'contract_issues';

    case NotOwner = 'not_owner';

    case LivesTooFar = 'lives_too_far';

    case LessThanAYear = 'less_than_a_year';

    case LatePayments = 'late_payments';

    case OutOfCoverage = 'out_of_coverage';

    case OtherFamilyMembers = 'other_family_members';

    case OutOfCoverageApproved = 'out_of_coverage_approved';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NoChildren => 'No tiene hijos',
            self::ContractIssues => 'Problemas con el contrato',
            self::NotOwner => 'No es dueño del terreno',
            self::LivesTooFar => 'Vive muy lejos del terreno',
            self::LessThanAYear => 'Tiene menos de un año con el terreno',
            self::LatePayments => 'Atrasado con los pagos',
            self::OutOfCoverage => 'Vive en una colonia no atendida o de riesgo',
            self::OtherFamilyMembers => 'Abuelos - Tios - Hermanos',
            self::OutOfCoverageApproved => 'Fuera de Zona',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NoChildren => '#ef4444',
            self::ContractIssues => '#f97316',
            self::NotOwner => '#f59e0b',
            self::LivesTooFar => '#eab308',
            self::LessThanAYear => '#84cc16',
            self::LatePayments => '#22c55e',
            self::OutOfCoverage => '#10b981',
            self::OtherFamilyMembers => '#06b6d4',
            self::OutOfCoverageApproved => '#8b5cf6',
        };
    }

    public function message(): string
    {
        return match ($this) {
            self::NoChildren => 'Nuestro programa está enfocado en apoyar a familias que tengan hijos menores de edad viviendo con ellos. En el caso de personas adultas mayores, es posible aplicar únicamente si tienen menores bajo su tutela legal y pueden presentar la documentación que lo compruebe. Además, los menores deben estar actualmente inscritos en primaria o secundaria.',
            self::ContractIssues => 'Para nosotros es indispensable que usted o su pareja sea el propietario del terreno, cuente con documentación legal que lo acredite, esperamos que usted pueda encontrar la ayuda que usted necesita.',
            self::NotOwner => 'Para nosotros es indispensable que usted o su pareja sea el propietario del terreno, cuente con documentación legal que lo acredite, esperamos que usted pueda encontrar la ayuda que usted necesita.',
            self::LivesTooFar => 'Solo estamos considerando a las familias que viven en su terreno o en la misma colonia. Esperamos que encuentre la ayuda que usted necesita. Si esta situación cambia en el futuro, usted podrá volver a aplicar después de tener más de 8 meses viviendo cerca de su terreno o en el mismo.',
            self::LessThanAYear => 'Necesitas tener una antigüedad mínima de un año con tu terreno o vivir en tu terreno por al menos 8 meses para poder aplicar y quedando sujeto a revisiones o espera dependiendo del cumplimiento de tus pagos mensuales por tu terreno.',
            self::LatePayments => 'Vimos que tienes pagos atrasados con tu terreno. Por ahora no podemos seguir con tu proceso, ya que el programa pide que estés al corriente con tus pagos para participar.',
            self::OutOfCoverage => 'Lamentablemente, no estamos construyendo en la colonia donde se encuentra su terreno 😔. Nos encantaría poder ayudar a todos, pero nuestros recursos son limitados. ¡No somos la única organización construyendo hogares 🏠! Le animamos a que continúe investigando para ver si hay otras organizaciones trabajando en su colonia. ¡Gracias por su comprensión y esperamos que encuentre la ayuda que necesita!',
            self::OtherFamilyMembers => 'Sabemos que muchas familias hacen un gran esfuerzo para cuidar de sus hijos o nietos, y entendemos la necesidad que existe. Lamentablemente, en este momento no nos es posible apoyar a todos los casos. Este apoyo está dirigido únicamente a abuelos, tíos o hermanos que son los responsables permanentes del cuidado de los menores, ya sea porque cuentan con su custodia legal o porque los padres ya no están a cargo de ellos. Si usted cuida a los niños mientras sus padres trabajan, quienes pueden realizar el registro son los padres, siempre y cuando el terreno donde se construiría la casa esté a nombre de ellos. Agradecemos mucho su comprensión y le deseamos muchas bendiciones.',
            self::OutOfCoverageApproved => 'Revisamos cuidadosamente la ubicación de tu terreno y, lamentablemente, está fuera de las zonas donde podemos construir. Por este motivo, *tu proceso no podrá continuar*. Te pedimos que *ya no te presentes a la entrevista*, ya que no será posible seguir con la solicitud. Sabemos que esta noticia puede ser decepcionante y nos gustaría poder apoyar a todas las familias que lo necesitan. Oramos para que pronto encuentren la ayuda que buscan. Dios les bendiga.',
        };
    }
}
