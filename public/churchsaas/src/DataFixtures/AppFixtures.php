<?php

namespace App\DataFixtures;

use App\Entity\Announcement;
use App\Entity\Campaign;
use App\Entity\Church;
use App\Entity\Deacon;
use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\FinancialCategory;
use App\Entity\InventoryItem;
use App\Entity\InventoryMovement;
use App\Entity\Member;
use App\Entity\Ministry;
use App\Entity\Notification;
use App\Entity\PastoralAppointment;
use App\Entity\PrayerRequest;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\SchoolClass;
use App\Entity\ServiceSlot;
use App\Entity\Student;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Dados de demonstração: uma igreja (tenant) com usuários de cada perfil,
 * membros, diáconos, escalas e lançamentos financeiros.
 *
 * Rode: php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $em): void
    {
        // ---- Tenant ----
        $church = (new Church())
            ->setName('Igreja Betel — Sede')
            ->setSlug('betel-sede')
            ->setEmail('contato@moriadedeus.org')
            ->setPhone('(31) 90000-0000');
        $em->persist($church);

        // ---- Usuários (um por perfil) ----
        $perfis = [
            ['admin@moriadedeus.org', 'Administrador Geral', ['ROLE_ADMIN']],
            ['prvalmer@moriadedeus.org', 'Pr. Valmer Moreira', ['ROLE_PASTOR']],
            ['secretaria@moriadedeus.org', 'Ana Secretária', ['ROLE_SECRETARIO']],
            ['tesouraria@moriadedeus.org', 'Carlos Tesoureiro', ['ROLE_TESOUREIRO']],
            ['diacono@moriadedeus.org', 'Diác. Pedro', ['ROLE_DIACONO']],
        ];
        $deaconUser = null;
        foreach ($perfis as [$mail, $nome, $roles]) {
            $u = (new User())->setEmail($mail)->setFullName($nome)->setRoles($roles)->setChurch($church);
            $u->setPassword($this->hasher->hashPassword($u, 'senha123'));
            $em->persist($u);
            if ($mail === 'diacono@moriadedeus.org') {
                $deaconUser = $u; // será vinculado a um cadastro de diácono mais abaixo
            }
        }

        // ---- Ministérios ----
        $minLouvor = (new Ministry())->setName('Louvor')->setChurch($church);
        $minDiaconia = (new Ministry())->setName('Diaconia')->setChurch($church);
        $em->persist($minLouvor);
        $em->persist($minDiaconia);

        // ---- Categorias financeiras ----
        foreach ([['Dízimos','entrada'],['Ofertas','entrada'],['Energia','saida'],['Manutenção','saida']] as [$n,$d]) {
            $em->persist((new FinancialCategory())->setName($n)->setDirection($d)->setChurch($church));
        }

        // ---- Campanha ----
        $em->persist((new Campaign())->setName('Construção do Templo')->setGoalAmount('150000.00')->setChurch($church));

        // ---- Membros + diáconos ----
        $nomes = ['Pedro Alves','Marcos Souza','Lucas Dias','Tiago Nunes','Mateus Rocha','Rute Lima','Ester Gomes'];
        $deacons = [];
        foreach ($nomes as $i => $nome) {
            $m = (new Member())
                ->setFullName($nome)
                ->setChurchRole($i < 5 ? 'diacono' : 'membro')
                ->setStatus(Member::STATUS_ATIVO)
                ->setMinistry($i < 5 ? $minDiaconia : $minLouvor)
                ->setBirthDate(new \DateTimeImmutable(sprintf('19%02d-%02d-15', 80 + $i, ($i % 12) + 1)))
                ->setPhone('(31) 9'.rand(1000,9999).'-'.rand(1000,9999))
                ->setChurch($church);
            $em->persist($m);

            if ($i < 5) {
                $d = (new Deacon())->setMember($m)->setActive(true)->setChurch($church);
                $d->setLeader($i === 1); // Marcos Souza é o líder do diaconato
                $em->persist($d);
                $deacons[] = $d;
            }
        }

        // Vincula o usuário de login "diacono@moriadedeus.org" ao 1º diácono (Pedro Alves),
        // um diácono comum: ele poderá aceitar e se desmarcar de vagas (auto-inscrição).
        if ($deaconUser !== null) {
            $deaconUser->setMember($deacons[0]->getMember());
        }

        // ---- Escalas + designações ----
        $tipos = ['culto','santa_ceia','recepcao','estacionamento','limpeza'];
        foreach ($tipos as $k => $tipo) {
            $s = (new Schedule())
                ->setType($tipo)
                ->setTitle('Escala de '.Schedule::TYPES[$tipo].' — Domingo')
                ->setScheduledAt(new \DateTimeImmutable('+'.($k + 1).' days 19:00'))
                ->setLocation('Templo Sede')
                ->setChurch($church);
            $em->persist($s);

            foreach (array_slice($deacons, 0, 3) as $j => $d) {
                $a = (new ScheduleAssignment())
                    ->setSchedule($s)->setDeacon($d)
                    ->setPosition('Posto '.($j + 1))
                    ->setPresence(ScheduleAssignment::PRESENCE_ESCALADO)
                    ->setChurch($church);
                $em->persist($a);
            }

            // Vagas de serviço abertas para auto-inscrição (o diácono aceita)
            foreach (['agua', 'portaria', 'estacionamento', 'limpeza'] as $atividade) {
                $slot = (new ServiceSlot())
                    ->setSchedule($s)
                    ->setActivity($atividade)
                    ->setChurch($church);
                // deixa "água" já aceita pelo diácono de login (Pedro Alves),
                // para demonstrar a tela "Minhas escalas" e a desmarcação com motivo
                if ($atividade === 'agua') {
                    $slot->assignTo($deacons[0]);
                }
                $em->persist($slot);
            }
        }

        // ---- Lançamentos financeiros ----
        for ($i = 0; $i < 20; $i++) {
            $entrada = $i % 3 !== 0;
            $t = (new Transaction())
                ->setDirection($entrada ? 'entrada' : 'saida')
                ->setKind($entrada ? ($i % 2 ? 'dizimo' : 'oferta') : 'despesa')
                ->setAmount((string) rand(50, 2000).'.00')
                ->setOccurredAt(new \DateTimeImmutable('-'.rand(0, 300).' days'))
                ->setDescription($entrada ? 'Contribuição culto' : 'Despesa operacional')
                ->setChurch($church);
            $em->persist($t);
        }

        // ---- Pastoral: agenda + pedidos de oração ----
        $ap = (new PastoralAppointment())
            ->setType('visita')
            ->setScheduledAt(new \DateTimeImmutable('+2 days 15:00'))
            ->setSubject('Visita à família Alves')
            ->setStatus('agendado')
            ->setChurch($church);
        $em->persist($ap);
        foreach (['Maria', 'João', 'Confidencial'] as $i => $nome) {
            $pr = (new PrayerRequest())
                ->setRequesterName($nome)
                ->setRequest('Motivo de oração de exemplo.')
                ->setConfidential($nome === 'Confidencial')
                ->setChurch($church);
            $em->persist($pr);
        }

        // ---- Evento com inscrições ----
        $ev = (new Event())
            ->setType('congresso')
            ->setName('Congresso de Família 2026')
            ->setStartsAt(new \DateTimeImmutable('+30 days 19:00'))
            ->setLocation('Templo Sede')
            ->setFee('30.00')
            ->setCapacity(200)
            ->setChurch($church);
        $em->persist($ev);
        foreach (['Ana Paula', 'Roberto Dias'] as $nome) {
            $reg = (new EventRegistration())
                ->setEvent($ev)->setParticipantName($nome)
                ->setChurch($church);
            $em->persist($reg);
        }

        // ---- Escola Bíblica: turma + alunos ----
        $turma = (new SchoolClass())
            ->setName('Classe de Adultos')
            ->setAgeGroup('Adultos')
            ->setChurch($church);
        $em->persist($turma);
        foreach (['Carlos', 'Fernanda', 'Juliana'] as $nome) {
            $al = (new Student())->setFullName($nome)->setChurch($church);
            $al->addClass($turma);
            $em->persist($al);
        }

        // ---- Comunicação: aviso no mural ----
        $av = (new Announcement())
            ->setTitle('Culto de Gratidão neste domingo')
            ->setBody('Convidamos toda a igreja para o culto especial de gratidão às 19h.')
            ->setChannel('mural')
            ->setAudience('todos')
            ->setSentAt(new \DateTimeImmutable())
            ->setChurch($church);
        $em->persist($av);

        // ---- Estoque de doações (mantimentos e limpeza, sem valores) ----
        $itens = [
            ['Arroz 5kg', 'mantimento', 'pacote', 20, 10],
            ['Feijão 1kg', 'mantimento', 'kg', 5, 8],       // abaixo do mínimo → alerta
            ['Óleo de soja', 'mantimento', 'un', 15, 6],
            ['Detergente', 'limpeza', 'un', 12, 6],
            ['Sabão em pó', 'limpeza', 'pacote', 3, 5],      // abaixo do mínimo → alerta
            ['Papel higiênico', 'higiene', 'fardo', 8, 4],
        ];
        foreach ($itens as [$nome, $cat, $un, $qtd, $min]) {
            $it = (new InventoryItem())
                ->setName($nome)->setCategory($cat)->setUnit($un)
                ->setQuantity($qtd)->setMinQuantity($min)
                ->setChurch($church);
            $em->persist($it);

            $mov = (new InventoryMovement())
                ->setItem($it)->setDirection(InventoryMovement::IN)->setQuantity($qtd)
                ->setDonor('Doação da congregação')->setChurch($church);
            $em->persist($mov);
        }

        // ---- Notificação de boas-vindas para o diácono de login ----
        if ($deaconUser !== null) {
            $em->persist((new Notification())
                ->setUser($deaconUser)
                ->setTitle('Bem-vindo à Diaconia')
                ->setMessage('Você tem uma vaga de água aceita. Confira em "Minhas escalas".')
                ->setIcon('hand-thumbs-up')
                ->setChurch($church));
        }

        $em->flush();
    }
}
